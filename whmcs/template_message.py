# Primeiro nome: {firstName}
# Sobrenome: {lastName}
# número de celular: {phone}
# Número da Fatura: {invoiceNumber}
# Data de vencimento: {duedate}
# Fatura total: {duetotal}
# Tiket ID: {ticketID}
# Tiket title: {ticketTitle}

invoice_unpaid = "Olá, *{firstName} {lastName}* ,sua fatura digital de Nº: *{invoiceNumber}* já faturado não foi pago.\n\n Para evitar a suspensão do serviço, faça o login no portal do cliente https://rtop.com.br/index.php?rp=/login e efetue o pagamento com o valor nominal de *R$ {duetotal}* antes da data *{duedate}*, ou você também pode fazer uma transferência através do número de pix:\n- - - - - - - - - - - - - - - -\financeiro@rtop.com.br\n- - - - - - - - - - - - - - - -\n\nSe você fez um pagamento manual acima, responda com o comprovante de transferência.\n\nObrigado" 
invoice_paid = "Olá, *{firstName} {lastName}* a sua fatura digital de Nº: *{invoiceNumber}* já pago, \nSe precisar de assistência técnica entre em contato conosco\n\nObrigado" 
invoice_duedate = "Olá, *{firstName} {lastName}* a sua fatura digital de Nº: *{invoiceNumber}* já faturado dan dalam waktu tenggang.\n\nPara evitar a suspensão do serviço, faça o login no portal do cliente https://rtop.com.br/index.php?rp=/login e efetue o pagamento com o valor nominal de *Rp. {duetotal}* data máxima *{duedate}*, ou você também pode fazer uma transferência através do número de pix:\n- - - - - - - - - - - - - - - -\nfinanceiro@rtop.com.br\n- - - - - - - - - - - - - - - -\n\nSe você fez um pagamento manual acima, responda com o comprovante de transferência.\n\nObrigado" 
invoice_comingTerminate = "Olá, *{firstName} {lastName}* a sua fatura digital de Nº: *{invoiceNumber}* status não pago.\n\nPara evitar o encerramento dos serviços, faça o login no portal do cliente https://rtop.com.br/index.php?rp=/login e efetue o pagamento com o valor nominal de *Rp. {duetotal}* data máxima *hoje*, ou você também pode fazer uma transferência através do número de pix:\n- - - - - - - - - - - - - - - -\nfinanceiro@rtop.com.br\n- - - - - - - - - - - - - - - -\n\nSe você fez um pagamento manual acima, responda com o comprovante de transferência.\n\nObrigado" 
new_ticket = "Olá, *{firstName} {lastName}* tiket: *{ticketID}* por ticket *{ticketTitle}* nós recebemos\nResponderemos o mais breve possível, verifique seu ticket na área do portal\n\nObrigado" 
reply_ticket = "Olá, *{firstName} {lastName}* tiket: *{ticketID}* por ticket *{ticketTitle}* nós respondemos\n Verifique as atualizações de seus tickets na área do portal\n\nObrigado" 
