# Open Newsletter

Plugin de newsletter para WordPress, **livre e de código aberto** (GPL-2.0-ou-posterior). Listas de assinantes, compositor de e-mails, motor de envio e relatórios completos de aberturas e cliques, sem chave de licença, sem recursos bloqueados, sem telemetria e sem conexões externas.

> **Open Newsletter é um fork do plugin [Newsletter](https://wordpress.org/plugins/newsletter/) 8.9.2**, de Stefano Lissa & The Newsletter Team, distribuído sob a mesma licença (GPL-2.0-ou-posterior). Veja [CREDITS.md](CREDITS.md).

## O que muda em relação ao original

| Tema | Open Newsletter |
|---|---|
| Licença / premium | Nenhuma chave, nenhuma trava, nenhum aviso de upgrade ou compra. |
| Relatórios | Aberturas e cliques registrados ficam visíveis: lista por assinante (filtros combináveis de envio, erro, abertura e clique, também por lista; paginação; exportação CSV do filtro aplicado), URLs clicadas com contagem e quem clicou, histórico por assinante e "adicionar à lista" a partir de um filtro. |
| Botão CTA | O botão do compositor pode adicionar/remover o assinante de listas ao ser clicado (parâmetro assinado no link de rastreamento). |
| Importar/Exportar | Importação e exportação de assinantes em CSV embutidas (no original dependiam de um add-on separado). |
| Telemetria | Removida: sem feed de novidades, sem formulários de opt-in para terceiros, sem pedidos de avaliação, sem crons de coleta. |
| Conexões externas | Nenhuma. Sem verificação de licença, catálogo de add-ons, atualizador remoto, CDN, Google Fonts, Google Charts, Gravatar ou Giphy. A fonte Inter e todas as bibliotecas JS/CSS são empacotadas. |
| Atualizações | O plugin não consulta nenhum servidor. A oferta de atualização do WordPress.org é descartada localmente. |

## Compatibilidade e migração

O slug, o text domain, os prefixos (`Newsletter*`, `TNP_*`, `tnp-*`, `newsletter_*`) e o **esquema do banco são idênticos aos do original**. Você pode:

- instalar o Open Newsletter sobre um site que já usava o Newsletter e manter todos os dados (assinantes, listas, newsletters, estatísticas);
- voltar ao Newsletter original quando quiser (as alterações no banco são apenas aditivas).

**Importante:** o código usa a pasta `newsletter/` como slug. Use o ZIP gerado por `tools/build-zip.sh`, que contém a pasta `newsletter/`. Não deixe o Newsletter original e o Open Newsletter ativos ao mesmo tempo: o Open Newsletter detecta isso e não é carregado, exibindo um aviso.

Add-ons escritos para o plugin original continuam sendo detectados pelos mesmos nomes de classe/hook (por exemplo `NewsletterWoocommerce`). Eles não são distribuídos aqui nem consultados em nenhum catálogo remoto.

## Instalação

```bash
git clone https://github.com/fredpiuma/open-newsletter-for-wordpress.git
cd open-newsletter-for-wordpress
tools/build-zip.sh          # gera dist/open-newsletter-9.2.9.zip (com a pasta newsletter/)
```

Envie o ZIP em *Plugins → Adicionar novo → Enviar plugin* e ative.

Requisitos: WordPress 5.6+ e PHP 7.0+ (testado com PHP 8.3).

## Idiomas

Inclui português do Brasil (`languages/newsletter-pt_BR.po/.mo`), baseado nas traduções do plugin original com as strings novas adicionadas. Strings ainda sem tradução aparecem em inglês.

## Licença

GPL-2.0-ou-posterior. Veja [LICENSE](LICENSE). As bibliotecas de terceiros e suas licenças estão em [CREDITS.md](CREDITS.md).

---

### Precisa de uma solução sob medida para a sua loja?

Desenvolvo plugins, integrações e melhorias para WordPress e WooCommerce, do checkout ao pós-venda. Me conte o que você precisa e vamos encontrar o melhor caminho para o seu projeto.

**Frederico de Castro** · Senior Web Developer & Software Architect

[![Fale comigo no WhatsApp](https://img.shields.io/badge/Fale_comigo_no_WhatsApp-25D366?style=for-the-badge&logo=whatsapp&logoColor=white)](https://wa.me/5511914636944)
[![E-mail](https://img.shields.io/badge/E--mail-D14836?style=for-the-badge&logo=gmail&logoColor=white)](mailto:contato@fredericodecastro.com.br)
[![Site](https://img.shields.io/badge/Site-1F2937?style=for-the-badge&logo=googlechrome&logoColor=white)](https://www.fredericodecastro.com.br/links/)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-0A66C2?style=for-the-badge)](https://www.linkedin.com/in/fredericodecastro/)
[![Telegram](https://img.shields.io/badge/Telegram-2CA5E0?style=for-the-badge&logo=telegram&logoColor=white)](https://t.me/fredericomdecastro)
