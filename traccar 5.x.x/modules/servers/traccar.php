<?php
//Chamando Database
use WHMCS\Database\Capsule;
//Bloqueio de acesso direto
if(!defined("WHMCS")){
    die("Acesso negado. Power By Traccar Module.");
}
//Configurações de Informações
function traccar_MetaData(){
	//Criando Array
    return array(
        'DisplayName' => 'Traccar', //Nome
        'APIVersion' => '1.0', //Versão
        'RequiresServer' => true, //Se necessita de Servidor
        'DefaultNonSSLPort' => '8082', //Porta sem SSL
        'DefaultSSLPort' => '8080', //Porta com SSL
        'ServiceSingleSignOnLabel' => 'Abrir Plataforma', //Texto Botão de Abrir Painel[Cliente]
        'AdminSingleSignOnLabel' => 'Abrir Plataforma', //Texto Botão de Abrir Painel[Admin]
    );
}
//Configuração de Opções
function traccar_ConfigOptions(){
	//Criando Array
    return array(
    	//Campo de Cerca Virtual
        'Cerca Virtual: Somente leitura?' => array(
            'Type' => 'text', //Tipo de Campo
            'Size' => '5', //Tamanho do Campo
            'Default' => 'false', //Resultado padrão
            'Description' => 'false ou true', //Descrição
        ),
        //Campo de Bloqueio
        'Bloqueio - Dispositivo somente leitura?' => array(
            'Type' => 'text', //Tipo de Campo
            'Size' => '5', //Tamanho do Campo
            'Default' => 'false', //Resultado padrão
            'Description' => 'false ou true', //Descrição
        ),
        //Campo de Limite de veículos
        'Limite de Veículos' => array(
            'Type' => 'text', //Tipo de Campo
            'Size' => '3', //Tamanho do Campo
            'Default' => '1', //Resultado padrão
            'Description' => 'Quantidade de Veículos', //Descrição
        ),
        //Campo de Limite de usuários
        'Limite de Usuários' => array(
            'Type' => 'text', //Tipo de Campo
            'Size' => '3', //Tamanho do Campo
            'Default' => '1', //Resultado padrão
            'Description' => 'Quantidade de Usuários', //Descrição
        ),
        //Campo Limite de Comandos
        'Limite de Comandos?' => array(
            'Type' => 'text', //Tipo de Campo
            'Size' => '5', //Tamanho do Campo
            'Default' => 'false', //Resultado padrão
            'Description' => 'false ou true', //Descrição
        ),
    );
}
//Ação: Criação
function traccar_CreateAccount($params){
	//Capsula contra erros
	try{
		//Montando usuário padrão
		$usuario = $params['clientsdetails']['email'];
		//Registrando usuário padrão
		$params['model']->serviceProperties->save(['Username' => $usuario]);
		//Informações de API Codificadas
		$info = json_encode(array(
	        "name" => $params['clientsdetails']['firstname'].' '.$params['clientsdetails']['lastname'], //Nome Completo do cliente
	        "email" => $usuario, //Usuário
	        "phone" => $params['clientsdetails']['phonenumber'], //Telefone
	        "login" => $usuario, //Usuário
	        "password" => $params['password'], //Senha
	 	    "readonly" => $params['configoption1'],
	        "administrator" => "false", //Se é administrador ou não
	        "map" => "googlec", //Tipo de mapa
	        "latitude" => "-14.690029", //Latitude da localização
	        "longitude" => "-51.584069", //Longitude da localização
	        "zoom" => "10", //Quantidade de Zom
	        "twelveHourFormat" => "false", //Formato de 12H
	        "coordinateFormat" => "dd", //Formato de Cordenadas
	        "disabled" => "false", //Desativar Cadastro
	        "deviceLimit" => $params['configoption3'], //Limite de Veiculos
	        "userLimit" => $params['configoption4'], //Limite de Usuários
	        "deviceReadonly" => $params['configoption2'], //Modo Leitura
	        "limitCommands" => $params['configoption5'], //Limitar Comandos
		));
		//Montando CURL
		$cabecario = array(); //Criando Array de Cabeçario
        $cabecario[] = 'Content-type: application/json'; //Setando dados para JSON
        $cabecario[] = 'Content-Length: '.strlen($info); //Criando tamanho de resposta
        $cabecario[] = 'Authorization: Basic '.base64_encode($params['serverusername'].":".$params['serverpassword']); //Autenticação
		$curl = curl_init("http://".$params['serverip'].":".$params['serverport']."/api/users/"); //Caminho da API
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Retorno de Dados
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecario); //Setando cabeçario na requisição
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); //Metodo de Envio
        curl_setopt($curl, CURLOPT_POSTFIELDS, $info); //Anexo de arquivos de informações
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //Ignorar Verificação de SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //Ignorar Verificação de SSL
        $resultado = curl_exec($curl); //Resultado do CURL
        curl_close($curl); //Fechando CURL
        $json = json_decode($resultado); //Dados JSON
        //Verificando se o retorno teve JSON
        if($json->id!=""){
        	//Bind de dados do MySQL
        	try{
		        //Obtendo informações do MYSQL (campo personalizado)
		        foreach(Capsule::table('tblcustomfields')->where('relid', $params['pid'])->where('fieldname', 'ID')->get() as $tblcustomfields){
		        	$campo_id = $tblcustomfields->id;
				}
				//Query para ver se não tem registros existentes
				$contagem_resultado = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $campo_id)->where('relid', $params['serviceid'])->count();
				//Checando contagem
				if($contagem_resultado == "0"){
					//Requisitando PDO
					$pdo = Capsule::connection()->getPdo();
					//Criando transação no PDO
					$pdo->beginTransaction();
					//Montando Query
					$querytr = $pdo->prepare('insert into tblcustomfieldsvalues (fieldid, relid, value) values (:fieldid, :relid, :value)');
				    //Execução de comando
				    $querytr->execute([
			            ':fieldid' 	=> $campo_id,
			            ':relid'	=> $params['serviceid'],
			            ':value'	=> $json->id,
				    ]);
				    //Fechando PDO
			    	$pdo->commit();
			    }
			    //Caso já existir
			    else{
			    	//Atualizar em vez de inserir
			    	Capsule::table('tblcustomfieldsvalues')->where('fieldid', $campo_id)->where('relid', $params['serviceid'])->update(['value' => $json->id,]);
			    }
				//Verificando se houve comunicação
				if($resultado!=""){
					//Sucesso
					return 'success';
				}
				//Caso tiver dado erro
				else{
					//Erro
					return 'Falha na comunicação com servidor';
				}
			}
			//Caso tiver tido algum problema com insert/consulta DB
			catch (\Exception $e){
				//Cancelar transação do PDO
				$pdo->rollBack();
				//Erro
				return 'Erro no MySQL: {$e->getMessage()}';
			}
		}
		//Caso tiver ocorrido algum erro
		else{
			//Verificando erro de duplicação
			if(strpos($resultado, 'Duplicate entry') !== false){
				//Duplicação de E-mail(e-mail existente)
				if(strpos($resultado, 'uk_user_email') !== false){
					//Erro
					return 'E-mail de cadastro já existente';
				}
				else{
					//Erro
					return 'Duplicação desconhecida: '.$resultado.'';
				}
			}
			else{
				//Erro
				return 'Erro desconhecido '.$resultado.'';
			}
		}
	}
	//Caso ocorreu algum erro
	catch(Exception $e){
		//Erro
		return 'Falha em comunicação com servidor';
	}
}
//Ação: Suspender
function traccar_SuspendAccount($params){
	//Capsula contra erros
	try{
		
		//Informações de API Codificadas
		$info = json_encode(array(
			"id" => $params['customfields']['ID'], //ID Cliente
	        "name" => $params['clientsdetails']['firstname'].' '.$params['clientsdetails']['lastname'], //Nome Completo do cliente
	        "email" => $params['username'], //Usuário
	        "phone" => $params['clientsdetails']['phonenumber'], //Telefone
	        "login" => $params['username'], //Usuário
	        //"password" => $params['password'], //Senha
	        
	        "readonly" => $params['configoption1'],
	        "administrator" => "false", //Se é administrador ou não
	        //"map" => "googlec", //Tipo de mapa
	        //"latitude" => "-14.690029", //Latitude da localização
	        //"longitude" => "-51.584069", //Longitude da localização
	        //"zoom" => "4", //Quantidade de Zom
	        "twelveHourFormat" => "false", //Formato de 12H
	        "coordinateFormat" => "dd", //Formato de Cordenadas
	        "disabled" => "true", //Desativar Cadastro
	        "deviceLimit" => $params['configoption3'], //Limite de Veiculos
	        "userLimit" => $params['configoption4'], //Limite de Usuários
	        "deviceReadonly" => $params['configoption2'], //Modo Leitura
	        "limitCommands" => $params['configoption5'], //Limitar Comandos
		));
		//Montando CURL
		$cabecario = array(); //Criando Array de Cabeçario
        $cabecario[] = 'Content-type: application/json'; //Setando dados para JSON
        $cabecario[] = 'Content-Length: '.strlen($info); //Criando tamanho de resposta
        $cabecario[] = 'Authorization: Basic '.base64_encode($params['serverusername'].":".$params['serverpassword']); //Autenticação
		$curl = curl_init("http://".$params['serverip'].":".$params['serverport']."/api/users/".$params['customfields']['ID']); //Caminho da API
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Retorno de Dados
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecario); //Setando cabeçario na requisição
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); //Metodo de Envio
        curl_setopt($curl, CURLOPT_POSTFIELDS, $info); //Anexo de arquivos de informações
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); //Requisição base
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //Ignorar Verificação de SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //Ignorar Verificação de SSL
        $resultado = curl_exec($curl); //Resultado do CURL
        curl_close($curl); //Fechando CURL
        $json = json_decode($resultado); //Dados JSON
        //Verificando se o retorno teve JSON
        if($json->id!=""){
        	//Verificando se houve comunicação
			if($resultado!=""){
				//Sucesso
				return 'success';
			}
			//Caso tiver dado erro
			else{
				//Erro
				return 'Falha na comunicação com servidor';
			}
		}
		//Caso tiver ocorrido algum erro
		else{
			//Verificando erro de point
			if(strpos($resultado, 'NullPointerException') !== false){
				//Erro
				return 'A conta não existe.';
			}
			else{
				//Erro
				return 'Erro desconhecido '.$resultado.'';
			}
		}
	}
	//Caso ocorreu algum erro
	catch(Exception $e){
		//Erro
		return 'Falha em comunicação com servidor';
	}
}
//Ação: Reativar
function traccar_UnsuspendAccount($params){
	//Capsula contra erros
	try{
		
		//Informações de API Codificadas
		$info = json_encode(array(
			"id" => $params['customfields']['ID'], //ID Cliente
	        "name" => $params['clientsdetails']['firstname'].' '.$params['clientsdetails']['lastname'], //Nome Completo do cliente
	        "email" => $params['username'], //Usuário
	        "phone" => $params['clientsdetails']['phonenumber'], //Telefone
	        "login" => $params['username'], //Usuário
	        //"password" => $params['password'], //Senha
	        
	        "readonly" => $params['configoption1'],
	        "administrator" => "false", //Se é administrador ou não
	        "map" => "googlec", //Tipo de mapa
	        "latitude" => "-14.690029", //Latitude da localização
	        "longitude" => "-51.584069", //Longitude da localização
	        "zoom" => "10", //Quantidade de Zom
	        "twelveHourFormat" => "false", //Formato de 12H
	        "coordinateFormat" => "dd", //Formato de Cordenadas
	        "disabled" => "false", //Desativar Cadastro
	        "deviceLimit" => $params['configoption3'], //Limite de Veiculos
	        "userLimit" => $params['configoption4'], //Limite de Usuários
	        "deviceReadonly" => $params['configoption2'], //Modo Leitura
	        "limitCommands" => $params['configoption5'], //Limitar Comandos
		));
		//Montando CURL
		$cabecario = array(); //Criando Array de Cabeçario
        $cabecario[] = 'Content-type: application/json'; //Setando dados para JSON
        $cabecario[] = 'Content-Length: '.strlen($info); //Criando tamanho de resposta
        $cabecario[] = 'Authorization: Basic '.base64_encode($params['serverusername'].":".$params['serverpassword']); //Autenticação
		$curl = curl_init("http://".$params['serverip'].":".$params['serverport']."/api/users/".$params['customfields']['ID']); //Caminho da API
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Retorno de Dados
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecario); //Setando cabeçario na requisição
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); //Metodo de Envio
        curl_setopt($curl, CURLOPT_POSTFIELDS, $info); //Anexo de arquivos de informações
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); //Requisição base
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //Ignorar Verificação de SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //Ignorar Verificação de SSL
        $resultado = curl_exec($curl); //Resultado do CURL
        curl_close($curl); //Fechando CURL
        $json = json_decode($resultado); //Dados JSON
        //Verificando se o retorno teve JSON
        if($json->id!=""){
        	//Verificando se houve comunicação
			if($resultado!=""){
				//Sucesso
				return 'success';
			}
			//Caso tiver dado erro
			else{
				//Erro
				return 'Falha na comunicação com servidor';
			}
		}
		//Caso tiver ocorrido algum erro
		else{
			//Verificando erro de point
			if(strpos($resultado, 'NullPointerException') !== false){
				//Erro
				return 'A conta não existe.';
			}
			else{
				//Erro
				return 'Erro desconhecido '.$resultado.'';
			}
		}
	}
	//Caso ocorreu algum erro
	catch(Exception $e){
		//Erro
		return 'Falha em comunicação com servidor';
	}
}
//Ação: Remover/Terminar
function traccar_TerminateAccount($params){
	//Capsula contra erros
	try{
		
				//Informações de API Codificadas
		$info = json_encode(array(
			"id" => $params['customfields']['ID'], //ID Cliente
	        "name" => $params['clientsdetails']['firstname'].' '.$params['clientsdetails']['lastname'], //Nome Completo do cliente
	        "email" => $params['username'], //Usuário
	        "phone" => $params['clientsdetails']['phonenumber'], //Telefone
	        "login" => $params['username'], //Usuário
	        "password" => $params['password'], //Senha
	        
	        "readonly" => $params['configoption1'],
	        "administrator" => "false", //Se é administrador ou não
	        //"map" => "googlec", //Tipo de mapa
	        //"latitude" => "-14.690029", //Latitude da localização
	        //"longitude" => "-51.584069", //Longitude da localização
	        //"zoom" => "4", //Quantidade de Zom
	        "twelveHourFormat" => "false", //Formato de 12H
	        "coordinateFormat" => "dd", //Formato de Cordenadas
	        "disabled" => "false", //Desativar Cadastro
	        "deviceLimit" => $params['configoption3'], //Limite de Veiculos
	        "userLimit" => $params['configoption4'], //Limite de Usuários
	        "deviceReadonly" => $params['configoption2'], //Modo Leitura
	        "limitCommands" => $params['configoption5'], //Limitar Comandos
		));
		//Montando CURL
		$cabecario = array(); //Criando Array de Cabeçario
        $cabecario[] = 'Content-type: application/json'; //Setando dados para JSON
        $cabecario[] = 'Content-Length: '.strlen($info); //Criando tamanho de resposta
        $cabecario[] = 'Authorization: Basic '.base64_encode($params['serverusername'].":".$params['serverpassword']); //Autenticação
		$curl = curl_init("http://".$params['serverip'].":".$params['serverport']."/api/users/".$params['customfields']['ID']); //Caminho da API
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Retorno de Dados
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecario); //Setando cabeçario na requisição
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); //Metodo de Envio
        curl_setopt($curl, CURLOPT_POSTFIELDS, $info); //Anexo de arquivos de informações
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); //Requisição base
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //Ignorar Verificação de SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //Ignorar Verificação de SSL
        $resultado = curl_exec($curl); //Resultado do CURL
        curl_close($curl); //Fechando CURL
        //Verificando se o retorno teve JSON
        if($resultado===""){
        	//Sucesso
        	return 'success';
		}
		//Caso tiver ocorrido algum erro
		else{
			//Verificando erro de duplicação
			if(strpos($resultado, 'Duplicate entry') !== false){
				//Duplicação de E-mail(e-mail existente)
				if(strpos($resultado, 'uk_user_email') !== false){
					//Erro
					return 'E-mail de cadastro já existente';
				}
				else{
					//Erro
					return 'Duplicação desconhecida: '.$resultado.'';
				}
			}
			else{
				//Erro
				return 'Erro desconhecido '.$resultado.'';
			}
		}
	}
	//Caso ocorreu algum erro
	catch(Exception $e){
		//Erro
		return 'Falha em comunicação com servidor';
	}
}
//Ação: Alterar Senha
function traccar_ChangePassword($params){
	//Capsula contra erros
	try{
		
		//Informações de API Codificadas
		$info = json_encode(array(
			"id" => $params['customfields']['ID'], //ID Cliente
	        "name" => $params['clientsdetails']['firstname'].' '.$params['clientsdetails']['lastname'], //Nome Completo do cliente
	        "email" => $params['username'], //Usuário
	        "phone" => $params['clientsdetails']['phonenumber'], //Telefone
	        "login" => $params['username'], //Usuário
	        "password" => $params['password'], //Senha
	        
	        "readonly" => $params['configoption1'],
	        "administrator" => "false", //Se é administrador ou não
	        //"map" => "googlec", //Tipo de mapa
	        //"latitude" => "-14.690029", //Latitude da localização
	        //"longitude" => "-51.584069", //Longitude da localização
	        //"zoom" => "4", //Quantidade de Zom
	        "twelveHourFormat" => "false", //Formato de 12H
	        "coordinateFormat" => "dd", //Formato de Cordenadas
	        "disabled" => "false", //Desativar Cadastro
	        "deviceLimit" => $params['configoption3'], //Limite de Veiculos
	        "userLimit" => $params['configoption4'], //Limite de Usuários
	        "deviceReadonly" => $params['configoption2'], //Modo Leitura
	        "limitCommands" => $params['configoption5'], //Limitar Comandos
		));
		//Montando CURL
		$cabecario = array(); //Criando Array de Cabeçario
        $cabecario[] = 'Content-type: application/json'; //Setando dados para JSON
        $cabecario[] = 'Content-Length: '.strlen($info); //Criando tamanho de resposta
        $cabecario[] = 'Authorization: Basic '.base64_encode($params['serverusername'].":".$params['serverpassword']); //Autenticação
		$curl = curl_init("http://".$params['serverip'].":".$params['serverport']."/api/users/".$params['customfields']['ID']); //Caminho da API
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Retorno de Dados
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecario); //Setando cabeçario na requisição
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); //Metodo de Envio
        curl_setopt($curl, CURLOPT_POSTFIELDS, $info); //Anexo de arquivos de informações
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); //Requisição base
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //Ignorar Verificação de SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //Ignorar Verificação de SSL
        $resultado = curl_exec($curl); //Resultado do CURL
        curl_close($curl); //Fechando CURL
        $json = json_decode($resultado); //Dados JSON
        //Verificando se o retorno teve JSON
        if($json->id!=""){
        	//Verificando se houve comunicação
			if($resultado!=""){
				//Sucesso
				return 'success';
			}
			//Caso tiver dado erro
			else{
				//Erro
				return 'Falha na comunicação com servidor';
			}
		}
		//Caso tiver ocorrido algum erro
		else{
			//Verificando erro de point
			if(strpos($resultado, 'NullPointerException') !== false){
				//Erro
				return 'A conta não existe.';
			}
			else{
				//Erro
				return 'Erro desconhecido '.$resultado.'';
			}
		}
	}
	//Caso ocorreu algum erro
	catch(Exception $e){
		//Erro
		return 'Falha em comunicação com servidor';
	}
}
//Ação: Alterar Pacote
function traccar_ChangePackage($params){
	//Capsula contra erros
	try{
		
		//Informações de API Codificadas
		$info = json_encode(array(
			"id" => $params['customfields']['ID'], //ID Cliente
	        "name" => $params['clientsdetails']['firstname'].' '.$params['clientsdetails']['lastname'], //Nome Completo do cliente
	        "email" => $params['username'], //Usuário
	        "phone" => $params['clientsdetails']['phonenumber'], //Telefone
	        "login" => $params['username'], //Usuário
	        "password" => $params['password'], //Senha
	        
	        "readonly" => $params['configoption1'],
	        "administrator" => "false", //Se é administrador ou não
	        //"map" => "googlec", //Tipo de mapa
	        //"latitude" => "-14.690029", //Latitude da localização
	        //"longitude" => "-51.584069", //Longitude da localização
	        //"zoom" => "4", //Quantidade de Zom
	        "twelveHourFormat" => "false", //Formato de 12H
	        "coordinateFormat" => "dd", //Formato de Cordenadas
	        "disabled" => "false", //Desativar Cadastro
	        "deviceLimit" => $params['configoption3'], //Limite de Veiculos
	        "userLimit" => $params['configoption4'], //Limite de Usuários
	        "deviceReadonly" => $params['configoption2'], //Modo Leitura
	        "limitCommands" => $params['configoption5'], //Limitar Comandos
		));
		//Montando CURL
		$cabecario = array(); //Criando Array de Cabeçario
        $cabecario[] = 'Content-type: application/json'; //Setando dados para JSON
        $cabecario[] = 'Content-Length: '.strlen($info); //Criando tamanho de resposta
        $cabecario[] = 'Authorization: Basic '.base64_encode($params['serverusername'].":".$params['serverpassword']); //Autenticação
		$curl = curl_init("http://".$params['serverip'].":".$params['serverport']."/api/users/".$params['customfields']['ID']); //Caminho da API
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Retorno de Dados
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecario); //Setando cabeçario na requisição
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); //Metodo de Envio
        curl_setopt($curl, CURLOPT_POSTFIELDS, $info); //Anexo de arquivos de informações
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); //Requisição base
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //Ignorar Verificação de SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //Ignorar Verificação de SSL
        $resultado = curl_exec($curl); //Resultado do CURL
        curl_close($curl); //Fechando CURL
        $json = json_decode($resultado); //Dados JSON
        //Verificando se o retorno teve JSON
        if($json->id!=""){
        	//Verificando se houve comunicação
			if($resultado!=""){
				//Sucesso
				return 'success';
			}
			//Caso tiver dado erro
			else{
				//Erro
				return 'Falha na comunicação com servidor';
			}
		}
		//Caso tiver ocorrido algum erro
		else{
			//Verificando erro de point
			if(strpos($resultado, 'NullPointerException') !== false){
				//Erro
				return 'A conta não existe.';
			}
			else{
				//Erro
				return 'Erro desconhecido '.$resultado.'';
			}
		}
	}
	//Caso ocorreu algum erro
	catch(Exception $e){
		//Erro
		return 'Falha em comunicação com servidor';
	}
}
//Ação: Testar API
function traccar_TestConnection($params){
	//Criando Conexão
	$testar_conexao = @fsockopen($params['serverip'], $params['serverport']);
	//Conferindo se conseguiu conexão
	if(is_resource($testar_conexao)){
		//Verificando TCP Agent
		if(getservbyport($params['serverport'], 'tcp')==="us-cli"){
			//Fechando Conexão
			fclose($testar_conexao);
			//Resposta
			$sucesso = true;
		}
		//Caso o TCP agent seja diferente
		else{
			//Fechando Conexão
			fclose($testar_conexao);
			//Resposta
			$erro = 'TCP Agent não compativel: '.getservbyport($params['serverport'], 'tcp').'';
		}
	}
	//Caso tiver problemas na conexão com o servidor
	else{
		//Fechando Conexão
		fclose($testar_conexao);
		//Resposta
		$erro = 'Sem conexão com o servidor';
	}
	//Retorno
	return array(
        'success' => $sucesso,
        'error' => $erro,
    );
}
//Ação: Login Cliente
function traccar_ServiceSingleSignOn($params){
	//Capsula contra erros
	try{
		
		//Informações de API Codificadas
		$info = json_encode(array(
			"id" => $params['customfields']['ID'], //ID Cliente
	        "name" => $params['clientsdetails']['firstname'].' '.$params['clientsdetails']['lastname'], //Nome Completo do cliente
	        "email" => $params['username'], //Usuário
	        "phone" => $params['clientsdetails']['phonenumber'], //Telefone
	        "login" => $params['username'], //Usuário
	        "password" => $params['password'], //Senha
	        
	        "readonly" => $params['configoption1'],
	        "administrator" => "false", //Se é administrador ou não
	        //"map" => "googlec", //Tipo de mapa
	        //"latitude" => "-14.690029", //Latitude da localização
	        //"longitude" => "-51.584069", //Longitude da localização
	        //"zoom" => "4", //Quantidade de Zom
	        "twelveHourFormat" => "false", //Formato de 12H
	        "coordinateFormat" => "dd", //Formato de Cordenadas
	        "disabled" => "false", //Desativar Cadastro
	        "deviceLimit" => $params['configoption3'], //Limite de Veiculos
	        "userLimit" => $params['configoption4'], //Limite de Usuários
	        "deviceReadonly" => $params['configoption2'], //Modo Leitura
	        "limitCommands" => $params['configoption5'], //Limitar Comandos
		));
		//Montando CURL
		$cabecario = array(); //Criando Array de Cabeçario
        $cabecario[] = 'Content-type: application/json'; //Setando dados para JSON
        $cabecario[] = 'Content-Length: '.strlen($info); //Criando tamanho de resposta
        $cabecario[] = 'Authorization: Basic '.base64_encode($params['serverusername'].":".$params['serverpassword']); //Autenticação
		$curl = curl_init("http://".$params['serverip'].":".$params['serverport']."/api/users/".$params['customfields']['ID']); //Caminho da API
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //Retorno de Dados
        curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecario); //Setando cabeçario na requisição
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST"); //Metodo de Envio
        curl_setopt($curl, CURLOPT_POSTFIELDS, $info); //Anexo de arquivos de informações
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); //Requisição base
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); //Ignorar Verificação de SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //Ignorar Verificação de SSL
        $resultado = curl_exec($curl); //Resultado do CURL
        curl_close($curl); //Fechando CURL
        //Verificando se houve comunicação
		if($resultado!=""){
			//Sucesso
			
			return array("success" => true, "redirectTo" => $url);
		}
		//Caso tiver dado erro
		else{
			//Erro
			return array("success" => false, "errorMsg" => "Erro: conta não existe!");
		}
	}
	//Caso ocorreu algum erro
	catch(Exception $e){
		//Erro
		return array("success" => false, "errorMsg" => "Um erro desconhecido ocorreu: {$e->getMessage()}");
	}
}
//Ação: Login Admin
function traccar_AdminSingleSignOn($params){
    //Sucesso
	return array(
        'success' => true,
        'redirectTo' => "http://".$params['serverip'].":".$params['serverport'].""
    );
}
//Ação: Botões Clientarea
function traccar_ClientAreaCustomButtonArray(){
    return array(
        "Logar no Painel" => "autologin",
    );
}
//Ação: Redirecionamento AutoLogin
function traccar_autologin($params){
	//Capturando URL do WHMCS
	foreach(Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->get() as $tblconfiguration){
		$url_whmcs = $tblconfiguration->value; // URL do WHMCS
	}
	//variavel de retorno
	$variavel = '<meta http-equiv="refresh" content="0; url='.$url_whmcs.'/clientarea.php?action=productdetails&id='.$params['serviceid'].'&dosinglesignon=1">';
	//Criando Return para autologin
	return $variavel;
}
//Clientarea Template
function traccar_ClientArea($params){
	//Capturando URL do WHMCS
	foreach(Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->get() as $tblconfiguration){
		$url_whmcs = $tblconfiguration->value; // URL do WHMCS
	}
	//Retorno
	return array(
        'templatefile' => 'produto.tpl', //Template da página
         'templateVariables' => array(
            'autologin_link' => ''.$url_whmcs.'clientarea.php?action=productdetails&id='.$params['serviceid'].'&dosinglesignon=1',
            'alterar_senha' => ''.$url_whmcs.'clientarea.php?action=productdetails&id='.$params['serviceid'].'#tabChangepw',
            'usuario' => $params['username'],
            'email' => $params['clientsdetails']['email'],
        ),
	);
}
?>