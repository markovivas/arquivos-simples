# Simple File List

Um plugin simples para WordPress que permite o envio, organização e listagem de arquivos com categorias.

---

## Instalação

1. **Faça upload dos arquivos do plugin** para a pasta:
   ```
   wp-content/plugins/simple-file-list
   ```
2. **Ative o plugin** no painel do WordPress em **Plugins > Plugins instalados**.

---

## Como Usar

### Exibir o gerenciador de arquivos no site

Adicione o shortcode abaixo em qualquer página ou post onde deseja exibir o gerenciador de arquivos:

```
[simple_file_list]
```

---

## Funcionalidades

- **Upload de arquivos** (restrito a usuários logados com permissão)
- **Campos de descrição e categoria** para cada arquivo enviado
- **Tabela de arquivos** com miniatura, nome, categoria, tamanho, data e ações (abrir/baixar)
- **Filtro por categoria** integrado à tabela
- **Exclusão de arquivos** (apenas para usuários autorizados)
- **Limites configuráveis** de tamanho, quantidade e tipos de arquivos

---

## Configurações

Acesse **Configurações > File List** no menu do WordPress para:

- Definir o número máximo de arquivos por envio
- Definir o tamanho máximo de cada arquivo (MB)
- Definir as extensões de arquivos permitidas (ex: `jpg,png,pdf`)

---

## Observações

- Apenas usuários logados com permissão de upload podem enviar ou excluir arquivos.
- Os arquivos são armazenados em:  
  `wp-content/uploads/simple-file-list/`
- O plugin utiliza as cores e variáveis CSS do tema para melhor integração visual.

---

## Suporte

Para dúvidas ou sugestões, abra uma issue ou entre em contato com