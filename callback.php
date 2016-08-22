<?php
/* Scrip em PHP responsavel por enviar e-mail para que o colaborador retorne a chamada, gerar log no servidor
 * com as informações da chamada e fazer insert na base de dados
 *
 * Autor: Ivan de Almeida Cornedi
 * Email: iacornedi@gmail.com
 */

/* Como no dialplan é usado o comando System(), se faz necessario a linha abaixo para receber os parametros */
for ($i=1; $i < $argc; $i++) {parse_str($argv[$i]);}


/* Função para coneção na base local */
function conectardb(){
    mysql_connect("127.0.0.1","root","SENHA") or die ("Não consegui conectar!! O que você fez?");
    mysql_select_db("asterisk") or die ("erro de base");
}

/* Lembre-se de criar a tabela com callback_$CLIENTE */
$CLIENTE='Nome_do_cliente_aqui';

/* Adicione quantos emails quiser, só lembre-se da virgula */
$EMAILS="email1@email.com, email2@email.com, email3@email.com"

conectardb();
switch ($OPCAO_DIGITADA){

	/*
	 * Cliente deseja que retorne o contato assim que possivel
	 * Gera log no /var/log/callback.log
	 * Envia e-mail para o(s) destinario(s) setado(s) na variavel $EMAIL
	 * Faz insert na tabela callback_multiplus
	 */
	case 1:
  	    shell_exec('echo "[$(date +"%Y-%m-%d %H:%M:%S")] CALLID ' . $UNIQUEID . ' TELEFONE ' . $TELEFONE . ' OPÇÃO DIGITADA ' . $OPCAO_DIGITADA . ' [RETORNAR CONTATO]" >> /var/log/callback.log');
	    shell_exec('sendEmail -f callback@latam.stefanini.com -s 10.161.69.235:25 -t ' . $EMAILS  . ' -u "[RETORNAR] - Retornar contato" -m "Cliente com telefone ' . $TELEFONE . ' solicita retorno do contato."');

	     $INSERT_RETORNAR_CONTATO = "INSERT INTO asterisk.callback_$CLIENTE (data, callid, telefone, opcao_digitada) VALUES (now(),'$UNIQUEID','$TELEFONE','$OPCAO_DIGITADA');";
	     $resultado = mysql_query($INSERT_RETORNAR_CONTATO);
	break;

	/* Cliente irá aguardar atendimento na fila de espera
	 * Gera log no /var/log/callback.log
	 * Faz insert na tabela callback_multiplus
	 */
	case 2:
 	    shell_exec('echo "[$(date +"%Y-%m-%d %H:%M:%S")] CALLID ' . $UNIQUEID . ' OPÇÃO DIGITADA ' . $OPCAO_DIGITADA . ' [AGUARDAR NA FILA]" >> /var/log/callback.log');
	     $INSERT_AGUARDAR_NA_FILA =  "INSERT INTO asterisk.callback_$CLIENTE (data, callid, telefone, opcao_digitada) VALUES (now(),'$UNIQUEID','$TELEFONE','$OPCAO_DIGITADA');";
	     $resultado = mysql_query($INSERT_AGUARDAR_NA_FILA);
	break;

        /* Cliente irá retornar mais tarde
         * Gera log no /var/log/callback.log
         * Faz insert na tabela callback_multiplus
         */
	case 3:
	    shell_exec('echo "[$(date +"%Y-%m-%d %H:%M:%S")] CALLID ' . $UNIQUEID . ' OPÇÃO DIGIRTADA ' . $OPCAO_DIGITADA . ' [RETORNARÁ MAIS TARDE]" >> /var/log/callback.log');
       	    $INSERT_CLINENTE_RETORNARA = "INSERT INTO asterisk.callback_$CLIENTE (data, callid, telefone, opcao_digitada) VALUES (now(),'$UNIQUEID','$TELEFONE','$OPCAO_DIGITADA');";
	    $resultado = mysql_query($INSERT_CLINENTE_RETORNARA);
	break;

	/* Gera log de erro */
	default;
	   shell_exec('echo "[$(date +"%Y-%m-%d %H:%M:%S")] CALLID ' . $UNIQUEID . ' TELEFONE ' . $TELEFONE . ' OPÇÃO DIGIRTADA ' . $OPCAO_DIGITADA . ' [ERRO]" >> /var/log/callback.log');


}


?>
