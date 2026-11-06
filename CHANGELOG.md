# Changelog

## 9.3.0 — 2026-11-06

Portadas as correções e novidades do Newsletter original até a 9.2.9. O recurso de IA do original **não** foi portado, e nada de licença, telemetria, promoções ou conexão externa entrou.

### Segurança
- Descadastro e reativação passam a exigir nonce (ações `uc` e `rc`); o link do e-mail leva a uma página de confirmação antes de executar a ação. Nova ação `r` para a confirmação de reativação.
- Nonces nas telas de edição, criação e prévia de newsletters e na ação de manutenção de assinantes.
- O parâmetro `alert` só é tratado na página de perfil; `na` é sanitizado e ações vazias são ignoradas.

### Adicionado
- Novo motor de envio (`NewsletterEngine`): lock em `newsletter_engine_lock`, lotes com controle de tempo e recuperação automática uma hora depois de um erro fatal (`newsletter_send_error_recover`).
- Opção **Intervalo entre e-mails** (`send_delay`, em milissegundos) no lugar de `max_per_second`, com migração automática e aditiva (a opção antiga é mantida). Também pode ser fixada pela constante `NEWSLETTER_SEND_DELAY`.
- Intervalo real do cron (`NEWSLETTER_REAL_CRON_INTERVAL`, de 60 a 900 s) e diagnóstico em Sistema para jobs agendados no futuro distante.
- Logs por newsletter (menu **Logs** na edição) e **Versões** com visualização e restauração de qualquer versão salva.
- Botão **Test** no editor de HTML puro (para um e-mail ou para os assinantes de teste).
- Compositor: classe CSS própria por bloco, raio e borda dos botões, largura e fundo do botão, imagem destacada configurável (galeria e conteúdo), separador de 1 px, textos alternativos de imagem e outras correções de HTML.
- Aviso na visualização online de rascunhos; listas de páginas paginadas com marcação da página inicial e da página de posts.
- Fallback de `{name}` e `full_name` quando o assinante não tem nome; evento `newsletter_user_saved` ao salvar assinante no admin.

### Alterado
- A edição de newsletters usa o compositor v3 (o v2 continua para presets e automações).
- Corrigido o aviso de `search_page` indefinido na lista de assinantes.
- Versão `9.3.0`. Nenhuma mudança destrutiva no banco: tudo é aditivo e compatível com o original.

## 9.2.9 — 2026-11-06

### Adicionado
- Relatório de assinantes por newsletter com filtros combináveis (enviados sem erro, com erro, abriram, clicaram, abriram e não clicaram, não abriram), por lista (qualquer uma ou todas as marcadas), com exportação CSV do filtro aplicado.

### Alterado
- Versão `9.2.9`. O número acompanha a linha 9.2+ exigida por add-ons do Newsletter original (ex.: Import/Export, que só carrega a partir da 9.2.0). Nenhuma mudança de esquema do banco.

## 9.0.0 — 2026-11-05

Primeira versão do **Open Newsletter**.

### Adicionado
- Relatórios locais: assinantes por newsletter com filtros combináveis (enviados sem erro, com erro, abriram, clicaram, abriram e não clicaram, não abriram) e por lista (qualquer uma ou todas as marcadas), atalhos rápidos, paginação e exportação CSV do filtro aplicado (com coluna de listas); URLs clicadas com contagem e quem clicou; histórico de aberturas e cliques por assinante; adicionar à lista os assinantes de um filtro ou de um link clicado.
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
- Versão `9.0.0` (na época). Slug, text domain, prefixos e esquema do banco permanecem idênticos aos do original.
- A limpeza das opções antigas de telemetria/licença roda no upgrade; a opção `contract_key` é preservada para permitir a volta ao original.
