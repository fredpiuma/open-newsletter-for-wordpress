# Changelog

O histórico do plugin original (até a 8.9.2) está em [changelog-original.txt](changelog-original.txt).

## 9.0.0 — 2026-11-05

Primeira versão do **Open Newsletter**, fork do Newsletter 8.9.2.

### Adicionado
- Relatórios locais: assinantes por newsletter com filtros (abriram, não abriram, clicaram, não clicaram, erros), paginação e exportação CSV; URLs clicadas com contagem e quem clicou; histórico de aberturas e cliques por assinante; adicionar à lista os assinantes de um filtro ou de um link clicado.
- Botão CTA do compositor adiciona/remove listas ao clique (parâmetro assinado `tnp_lists`).
- Importação e exportação de assinantes em CSV.
- Fonte Inter empacotada localmente (`admin/fonts`).
- Tradução pt_BR (`languages/`).
- Trava contra o Newsletter original ativo e filtro que descarta a oferta de atualização do WordPress.org.
- `tools/build-zip.sh` (ZIP com a pasta `newsletter/`) e `tools/build-mo.py`.

### Removido
- Verificação de licença e todo o código/UI de licença, upgrade, compra e planos.
- Telas promocionais de Automated, Autoresponder, Flow e o catálogo remoto de Addons (a tela de Integrações agora é uma lista local estática).
- Telemetria: feed de novidades, formulários de inscrição em newsletter de terceiros (dashboard, rodapé e boas-vindas), pedido de avaliação e o cron `newsletter_update`.
- Conexões externas: Google Fonts, Google Charts, Gravatar, Giphy, Unsplash, imagem de teste hospedada no site original, DNS reverso na tela de status e atualizador de add-ons.
- Rebranding: nome, autor, URLs, logos, ícones, textos e links de documentação.

### Alterado
- `strftime()` (obsoleto no PHP 8.1) substituído por `date_i18n()`.
- Versão `9.0.0`. Slug, text domain, prefixos e esquema do banco permanecem idênticos aos do original.
- A limpeza das opções antigas de telemetria/licença roda no upgrade; a opção `contract_key` é preservada para permitir a volta ao original.
