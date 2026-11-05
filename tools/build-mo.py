#!/usr/bin/env python3
"""Compiles languages/newsletter-pt_BR.po into the .mo file (no gettext required).

Usage: python3 tools/build-mo.py languages/newsletter-pt_BR.po
"""
import ast
import struct
import sys


def parse_po(path):
    entries = []
    cur = None
    field = None

    def flush():
        nonlocal cur
        if cur is not None and (cur.get('msgid') is not None):
            entries.append(cur)
        cur = None

    with open(path, encoding='utf-8') as fh:
        for raw in fh:
            line = raw.rstrip('\n')
            if line.startswith('#'):
                if cur is not None and field is not None and field.startswith('msgstr'):
                    flush()
                continue
            if not line.strip():
                if cur is not None:
                    flush()
                field = None
                continue
            if line.startswith('msgctxt '):
                if cur is not None and field and field.startswith('msgstr'):
                    flush()
                cur = cur or {}
                cur['msgctxt'] = ast.literal_eval(line[8:])
                field = 'msgctxt'
            elif line.startswith('msgid_plural '):
                cur['msgid_plural'] = ast.literal_eval(line[13:])
                field = 'msgid_plural'
            elif line.startswith('msgid '):
                if cur is not None and field and field.startswith('msgstr'):
                    flush()
                cur = cur or {}
                cur['msgid'] = ast.literal_eval(line[6:])
                field = 'msgid'
            elif line.startswith('msgstr['):
                idx = int(line[7:line.index(']')])
                cur.setdefault('msgstr', {})[idx] = ast.literal_eval(line[line.index(']') + 2:])
                field = 'msgstr[%d]' % idx
            elif line.startswith('msgstr '):
                cur.setdefault('msgstr', {})[0] = ast.literal_eval(line[7:])
                field = 'msgstr[0]'
            elif line.startswith('"'):
                value = ast.literal_eval(line)
                if field.startswith('msgstr['):
                    idx = int(field[7:-1])
                    cur['msgstr'][idx] += value
                else:
                    cur[field] += value
    if cur is not None:
        flush()
    return entries


def build_mo(entries, out):
    messages = {}
    for e in entries:
        strs = e.get('msgstr', {})
        if not any(strs.values()):
            continue
        key = e['msgid']
        if 'msgctxt' in e:
            key = e['msgctxt'] + '\x04' + key
        if 'msgid_plural' in e:
            key = key + '\x00' + e['msgid_plural']
            value = '\x00'.join(strs[i] for i in sorted(strs))
        else:
            value = strs[0]
        messages[key] = value
    keys = sorted(messages)
    ids = b''
    strs = b''
    offsets = []
    for k in keys:
        kb = k.encode('utf-8')
        vb = messages[k].encode('utf-8')
        offsets.append((len(ids), len(kb), len(strs), len(vb)))
        ids += kb + b'\x00'
        strs += vb + b'\x00'
    n = len(keys)
    keystart = 7 * 4 + 16 * n
    valuestart = keystart + len(ids)
    koffsets = []
    voffsets = []
    for o1, l1, o2, l2 in offsets:
        koffsets += [l1, o1 + keystart]
        voffsets += [l2, o2 + valuestart]
    output = struct.pack('Iiiiiii', 0x950412de, 0, n, 7 * 4, 7 * 4 + n * 8, 0, 0)
    output += struct.pack('%di' % len(koffsets), *koffsets)
    output += struct.pack('%di' % len(voffsets), *voffsets)
    output += ids + strs
    with open(out, 'wb') as fh:
        fh.write(output)
    return n


if __name__ == '__main__':
    po = sys.argv[1]
    mo = po[:-3] + '.mo'
    print(mo, build_mo(parse_po(po), mo), 'messages')
