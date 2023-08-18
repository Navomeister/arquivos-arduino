<?php
    header("Access-Control-Allow-Origin: *");
    include_once("conexao.php");

    // login do banco (talvez não precise)
    // $usuario = "arduinos";

    // nomes de usuário permitidos
    $usuariosPermitidos = ["arduino1", "arduino2"];
    // $senhas = ["senha1", "senha2"]; desnecessário

    if (isset($_GET['numero_sala'])) {
        # code...
        // muda o status para inativo para caso haja erro
        $statusChg = 'UPDATE sala SET STATUS_SALA = "Inativo" WHERE NUMERO_SALA = ' . $_GET["numero_sala"];
        $result = $conn->query($statusChg);
    }

    // verificar as credencias recebidas
    if (!in_array($_GET['usuario'], $usuariosPermitidos)) {
        // se as credenciais estiverem erradas, retorna erro
        header('HTTP/1.0 401 Unauthorized');
        echo ('Sem Permissão.');
        exit;
    } else {
        header('Acess-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-type');
    
        // verifica o método de requisição
        $method = $_SERVER['REQUEST_METHOD'];
    
        // verifica o endpoint solicitado
        $endpoint = $_GET['endpoint'];
    
        // verificar os parâmetros de requisição
        $params = $_POST;
    
        // define uma resposta padrão
        $response = array(
            'status' => 'error',
            'message' => 'Resposta Padrão'
        );
        if ($method == 'GET') {
            if ($endpoint == 'salas') {
                // verificação de conexão com o banco
                if ($conn->connect_error) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Erro:' . $conn->connect_error
                    );
                } else {
                    // consulta o banco
                    $sql = 'SELECT * FROM sala WHERE NUMERO_SALA = ' . $_GET["numero_sala"];
                    $result = $conn->query($sql);
    
                    // verifica se o query retornou algo
                    if ($result->num_rows > 0) {
                        // muda o status para ativo já que houve retorno da api
                        $statusChg = 'UPDATE sala SET STATUS_SALA = "Ativo" WHERE NUMERO_SALA = ' . $_GET["numero_sala"];
                        $status = $conn->query($statusChg);

                        // consulta novamente para pegar com status ativo
                        $sql = 'SELECT * FROM sala WHERE NUMERO_SALA = ' . $_GET["numero_sala"];
                        $result = $conn->query($sql);

                        $salas = array();
                        $i = 0;
    
                        // adiciona as informações retornadas ao array
                        while ($row = $result->fetch_assoc()) {
                            $sala = array(
                                'id' => $row['ID_SALA'],
                                'nome' => $row['NOME_SALA'],
                                'numero' => $row['NUMERO_SALA'],
                                'status' => $row['STATUS_SALA']
                            );
    
                            $salas[$i] = $sala;
                            $i++;
                        }
    
                        // resposta da api com status sucesso e informações
                        $response = array(
                            'status' => 'success',
                            'salas' => $salas
                        );

                    } else {
                        // resposta para caso não haja retorno
                        $response = array(
                            'status' => 'error',
                            'message' => 'Erro 404: Sala Não Encontrada.'
                        );
                    }
    
                    // fecha conexão com o banco
                    $conn->close();
                }
    
            }
        }
    }

    // enviar respostas como json
    echo(json_encode($response));

?>