<?php
    header("Access-Control-Allow-Origin: *");
    include_once("conexao.php");

    // login do banco (talvez não precise)
    // $usuario = "arduinos";

    // nomes de usuário permitidos
    $usuarios = 'SELECT * FROM arduino;';
    $pegaUsuarios = $conn->query($usuarios);
    $usuariosPermitidos = array();
    $i = 0;
    while ($row = $pegaUsuarios->fetch_assoc()) {
        $usuario = array(
            'ID_ARDUINO' => $row['ID_ARDUINO'],
            'UNIQUE_ID' => $row['UNIQUE_ID']
        );

        $usuariosPermitidos[$i] = $usuario;
        $i++;
    }
    
    // $senhas = ["senha1", "senha2"]; desnecessário

    // se não for para cadastrar o arduino
    if ($_GET['endpoint'] != "cadastro") {
        // verificar as credencias recebidas
        if (!in_array($_GET['usuario'], $usuariosPermitidos)) {
            // se as credenciais estiverem erradas, retorna erro
            header('HTTP/1.0 401 Unauthorized');
            echo (var_dump($usuariosPermitidos));
            exit;
        } 
    }
    else {
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
                // muda o status para inativo para caso haja erro
                $statusChg = 'UPDATE arduino SET STATUS_ARDUINO = "Inativo" WHERE UNIQUE_ID = ' . $_GET["usuario"];
                $statusChgd = $conn->query($statusChg);
                
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
                        $statusChg = 'UPDATE arduino SET STATUS_ARDUINO = "Ativo" WHERE UNIQUE_ID = ' . $_GET["usuario"];
                        $statusChgd = $conn->query($statusChg);

                        // consulta novamente para pegar com status ativo
                        $sql = 'SELECT * FROM sala INNER JOIN arduino ON FK_ARDUINO = ID_ARDUINO WHERE arduino.UNIQUE_ID = "'. $_GET['usuario'] .'";';
                        $result = $conn->query($sql);

                        $salas = array();
                        $i = 0;
    
                        // adiciona as informações retornadas ao array
                        while ($row = $result->fetch_assoc()) {
                            $sala = array(
                                'id' => $row['ID_SALA'],
                                'nome' => $row['NOME_SALA'],
                                'numero' => $row['NUMERO_SALA']
                            );
    
                            $salas[$i] = $sala;
                            $i++;
                        }
    
                        // resposta da api com status sucesso e informações
                        $response = array(
                            'status' => 'success',
                            'salas' => $salas
                        );

                    } 
                    
                    else {
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
            // endpoint cadastro
            elseif ($endpoint == 'cadastro') {
                $sql = 'SELECT * FROM arduino WHERE UNIQUE_ID =' . $_GET['usuario'];
                $query = $conn->query($sql);
                $result = $query->fetch_assoc();
                if ($result) {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Erro: Arduino já cadastrado'
                    );
                }
                else {
                    $cad = 'INSERT INTO arduino(UNIQUE_ID, STATUS_ARDUINO, LAST_UPDATE) VALUES("'. $_GET['usuario'] .'", "Inativo", NOW());';
                    $result = $conn->query($cad);

                    $sql = 'SELECT * FROM arduino WHERE UNIQUE_ID =' . $_GET['usuario'];
                    $query = $conn->query($sql);
                    $result = $query->fetch_assoc();
                    $response = array(
                        'status' => 'success',
                        'uniqueid' => $result['UNIQUE_ID']
                    );
                }
            }
        }
    }

    // enviar respostas como json
    echo(json_encode($response));

?>