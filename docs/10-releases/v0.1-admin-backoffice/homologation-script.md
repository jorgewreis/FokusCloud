# Roteiro de homologacao da release 0.1

## Preparacao

1. Aplicar migrations e seeders em ambiente de homologacao.
2. Configurar credenciais sandbox do Mercado Pago.
3. Criar ao menos um `superadministrador`.
4. Criar ao menos um `administrador_comercial`.
5. Criar empresa cliente, usuario administrador da empresa e assinatura de
   teste.

## Fluxo 1 - Acesso interno

1. Acessar `/backoffice/acesso`.
2. Entrar com e-mail e senha de superadministrador.
3. Confirmar recebimento e validacao de MFA.
4. Verificar acesso ao painel.
5. Sair e confirmar que rotas protegidas deixam de responder como autenticadas.

Resultado esperado: sessao interna criada apenas apos MFA valido.

## Fluxo 2 - Perfis

1. Entrar como superadministrador.
2. Criar administrador comercial.
3. Entrar como administrador comercial.
4. Tentar acessar gestao de usuarios internos, publicacao final, auditoria
   completa, override e conciliacao corretiva.

Resultado esperado: interface e API bloqueiam acoes exclusivas de
superadministrador.

## Fluxo 3 - Catalogo

1. Criar plano em rascunho.
2. Editar preco, ordem e conteudo.
3. Validar publicacao como administrador comercial.
4. Publicar como superadministrador.
5. Conferir catalogo publico e checkout.
6. Pausar e arquivar item publicado.

Resultado esperado: somente item publicado, ativo e completo aparece para o
cliente.

## Fluxo 4 - Vouchers

1. Criar voucher percentual.
2. Criar voucher de assinatura gratuita.
3. Criar voucher de valor fixo.
4. Criar voucher de credito comercial.
5. Validar limites, validade e elegibilidade.
6. Resgatar voucher em assinatura de teste.
7. Conferir snapshot do resgate.

Resultado esperado: voucher valido altera condicao comercial e preserva
historico.

## Fluxo 5 - Assinaturas

1. Consultar assinatura de empresa.
2. Pausar assinatura com motivo.
3. Reativar assinatura com motivo.
4. Agendar cancelamento.
5. Executar troca de plano com upgrade.
6. Executar troca de plano com downgrade.
7. Testar override manual com superadministrador.

Resultado esperado: status, historico, snapshots e auditoria refletem cada
acao.

## Fluxo 6 - Mercado Pago sandbox

1. Criar checkout/preapproval sandbox.
2. Simular pagamento aprovado.
3. Simular pagamento recusado.
4. Reprocessar evento duplicado.
5. Simular divergencia entre status interno e gateway.
6. Revisar e corrigir divergencia como superadministrador.

Resultado esperado: billing responde ao gateway de forma idempotente e
auditavel.

## Fluxo 7 - Auditoria e alertas

1. Consultar auditoria como superadministrador.
2. Consultar auditoria como administrador comercial.
3. Conferir evento com motivo e before/after mascarado.
4. Conferir alerta financeiro, de seguranca, catalogo e auditoria/revisao.
5. Resolver alerta como superadministrador.

Resultado esperado: eventos e alertas existem com escopo e permissao corretos.

## Encerramento

1. Registrar resultado de cada fluxo.
2. Classificar falhas.
3. Corrigir falhas bloqueantes.
4. Atualizar [notas da release](release-notes.md).
5. Marcar a release como pronta somente se nao houver bloqueio.
