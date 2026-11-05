# Créditos e licenças

**Open Newsletter** — Copyright 2026 Frederico de Castro (https://www.fredericodecastro.com.br/links/), licença **GPL-2.0-ou-posterior** (veja `LICENSE`). Repositório: https://github.com/fredpiuma/open-newsletter-for-wordpress

## Bibliotecas de terceiros (pasta `vendor/` e outros)

| Componente | Versão | Licença | Observações |
|---|---|---|---|
| TinyMCE | 4.9.11 | LGPL-2.1 | `vendor/tinymce/license.txt` |
| Chart.js | 2.9.4 | MIT | `vendor/chartjs/LICENSE.md` |
| Select2 | 4.0.13 | MIT | `vendor/select2/LICENSE.md` |
| Spectrum (color picker) | — | MIT | `vendor/spectrum/LICENSE` |
| CodeMirror | — | MIT | cabeçalho do próprio arquivo |
| jQuery UI (CSS) | 1.12.1 | MIT | |
| jquery-modal | 0.9.2 | MIT | |
| JQVMap | 1.5.1 | MIT | |
| Toastify | 1.12.0 | MIT | |
| Font Awesome Free | — | CSS: MIT; fontes: SIL OFL 1.1; ícones SVG: CC BY 4.0 | `vendor/fa/LICENSE.txt` |
| Inter (fonte) | variável | SIL OFL 1.1 | `admin/fonts/LICENSE-Inter-OFL.txt`; baixada de fonts.gstatic.com/rsms/inter e empacotada localmente |
| driver.js | sem versão no arquivo | **a confirmar** (provavelmente MIT) | `vendor/driver/`; o arquivo não traz cabeçalho de licença |
| `main/js/welcome.js` | — | Apache-2.0 | Cabeçalho próprio; compatível com GPL-2.0-ou-posterior (via GPL-3.0). Avaliar substituição. |

## Imagens de procedência não documentada

A origem de parte das imagens não está documentada. Elas foram mantidas porque fazem parte dos modelos e temas, mas **a licença delas não pôde ser confirmada**:

- `images/social-1` … `images/social-4` (ícones de redes sociais)
- `emails/themes/*/images`, `emails/templates/*`, `emails/presets/*`, `emails/blocks/*/images`, `emails/tnp-composer/_assets`
- `main/images/dashboard`, `statistics/images`, `admin/images/subject`, `admin/images/{block-icon,template-icon,nomedia}.png`
- `emails/images/*` (inclui `automated.png` e `autoresponder.png`, usados só se os add-ons originais estiverem instalados)

Se a procedência de alguma delas for um problema para você, substitua-as ou remova-as; os modelos continuam funcionando com imagens próprias.

## Identidade visual do Open Newsletter

Logo, ícone de menu e `images/placeholder.png` foram criados para este fork (GPL-2.0-ou-posterior).

## Nomes de terceiros mantidos no código

O código mantém nomes de classes, hooks e plugins de terceiros apenas para detecção de integrações: WooCommerce, WPML (`SitePress`), Polylang, TranslatePress, Akismet, Elementor, Ultimate Member, Paid Memberships Pro, Easy Digital Downloads e add-ons compatíveis (`NewsletterWoocommerce`, `NewsletterReports`, `NewsletterAutomated`, `NewsletterAutoresponder`, `NewsletterImport`, `NewsletterApi`, `NewsletterExtensions` etc.). Nenhum deles é distribuído aqui.
