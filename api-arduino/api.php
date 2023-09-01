<?php
    header("Access-Control-Allow-Origin: *");
    include_once("conexao.php");

    // login do banco (talvez não precise)
    // $usuario = "arduinos";

    // nomes de usuário permitidos
    $usuarios = 'SELECT UNIQUE_ID FROM arduino WHERE STATUS_ARDUINO = "Ativo";';
    $pegaUsuarios = $conn->query($usuarios);
    $usuariosPermitidos = array();
    $i = 0;
    while ($row = $pegaUsuarios->fetch_assoc()) {
        $usuariosPermitidos[$i] = $row['UNIQUE_ID'];
        $i++;
    }

    $response = array(
        'status' => 'error',
        'message' => 'Algo deu errado.'
    );
    
    // $senhas = ["senha1", "senha2"]; desnecessário

    // se não for para cadastrar o arduino
    if ($_GET['endpoint'] != "cadastro") {
        // verificar as credencias recebidas
        if (!in_array($_GET['usuario'], $usuariosPermitidos)) {
            // se as credenciais estiverem erradas, retorna erro
            header('HTTP/1.0 401 Unauthorized');
            echo ("Usuário não autorizado. \n ID: ". $_GET['usuario']);
            exit;
        } 
    }
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-type');
    
        // verifica o método de requisição
        $method = $_SERVER['REQUEST_METHOD'];
    
        // verifica o endpoint solicitado
        $endpoint = $_GET['endpoint'];
    
        // verificar os parâmetros de requisição
        $params = $_GET;
    
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
                    $query = 'SELECT * FROM arduino WHERE UNIQUE_ID = "'. $_GET["usuario"] .'";';
                    $idArduino = $conn->query($query);
                    $pegaId = $idArduino->fetch_assoc();

                    $sql = 'SELECT * FROM sala WHERE FK_ARDUINO = '. $pegaId['ID_ARDUINO'];
                    $result = $conn->query($sql);
    
                    // verifica se o query retornou algo
                    if ($result) {
                        // muda o status para ativo já que houve retorno da api
                        $statusChg = 'UPDATE arduino SET STATUS_ARDUINO = "Ativo" WHERE UNIQUE_ID = ' . $_GET["usuario"];
                        $statusChgd = $conn->query($statusChg);

                        // consulta novamente para pegar com status ativo
                        $sql = 'SELECT * FROM sala INNER JOIN arduino ON FK_ARDUINO = ID_ARDUINO WHERE arduino.UNIQUE_ID = "'. $_GET['usuario'] .'";';
                        $result = $conn->query($sql);
                        $resposta = $result->fetch_assoc();
                        $nomeSala = $resposta['NOME_SALA'] ." ". $resposta['NUMERO_SALA'];

                        // $urlGET = '{url}/v1/synthesize?accept=audio%2Fwav&text='. $nomeSala .'&voice=pt-BR_IsabelaV3Voice';
                        $urlGET = 'https://dummyjson.com/products/1';
                        $urlPOST = '{url}/v1/synthesize?voice=pt-BR_IsabelaV3Voice';

                        // POST
                        // // use key 'http' even if you send the request to https://...
                        // $options = [
                        //     'http' => [
                        //         'header' => "Authorization: Bearer {token}\r\nContent-type: application/x-www-form-urlencoded\r\nAccept: audio/wav",
                        //         'method' => 'POST',
                        //         'data' => "{\"text\":\"". $nomeSala ."\"}",
                        //     ],
                        // ];

                        // $context = stream_context_create($options);
                        // $result = file_get_contents($urlPOST, false, $context);
                        // if ($result === false) {
                        //     /* Handle error */
                        // }

                        // $respostaAPI = var_dump($result);
                        
                        // GET
                        $respostaAPI = file_get_contents($urlGET);

                        // $salas = array();
                        // $i = 0;
    
                        // // adiciona as informações retornadas ao array
                        // while ($row = $result->fetch_assoc()) {
                        //     $sala = array(
                        //         'id' => $row['ID_SALA'],
                        //         'nome' => $row['NOME_SALA'],
                        //         'numero' => $row['NUMERO_SALA']
                        //     );
    
                        //     $salas[$i] = $sala;
                        //     $i++;
                        // }
    
                        // resposta da api com status sucesso e informações
                        $response = array(
                            'status' => 'success',
                            'sala' => $respostaAPI
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
            elseif ($endpoint == 'ativo') {
                $sql = "UPDATE arduino SET STATUS_ARDUINO = 'Ativo', LAST_UPDATE = NOW() WHERE UNIQUE_ID = '". $_GET['usuario'] ."';";
                $result = $conn->query($sql);
                $response = array(
                    'status' => 'success',
                    'status_arduino' => 'Ativo'
                );
            }
        }

    // enviar respostas como json
    echo(json_encode($response));

?>