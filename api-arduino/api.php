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
                        // $urlGET = 'https://dummyjson.com/products/1';
                        $urlPOST = 'https://eastus.tts.speech.microsoft.com/cognitiveservices/v1';

                        // POST
                        // use key 'http' even if you send the request to https://...
                        // $options = [
                        //     'http' => [
                        //         'header' => "Authorization: Bearer eyJhbGciOiJFUzI1NiIsImtpZCI6ImtleTEiLCJ0eXAiOiJKV1QifQ.eyJyZWdpb24iOiJlYXN0dXMiLCJzdWJzY3JpcHRpb24taWQiOiI5MTFkOTMyOWY3Mjg0NjVmYjEzMzQwYjU1ZGJlZTZkYSIsInByb2R1Y3QtaWQiOiJTcGVlY2hTZXJ2aWNlcy5GMCIsImNvZ25pdGl2ZS1zZXJ2aWNlcy1lbmRwb2ludCI6Imh0dHBzOi8vYXBpLmNvZ25pdGl2ZS5taWNyb3NvZnQuY29tL2ludGVybmFsL3YxLjAvIiwiYXp1cmUtcmVzb3VyY2UtaWQiOiIvc3Vic2NyaXB0aW9ucy8wNDU0MGNlYy0zMzRhLTQzMjctYWQyOC0zYzNhM2ExOWZlNTcvcmVzb3VyY2VHcm91cHMvZmFsYW50ZXMvcHJvdmlkZXJzL01pY3Jvc29mdC5Db2duaXRpdmVTZXJ2aWNlcy9hY2NvdW50cy9mYWxhbXVpdG9ldWVzcGVybyIsInNjb3BlIjoic3BlZWNoc2VydmljZXMiLCJhdWQiOiJ1cm46bXMuc3BlZWNoc2VydmljZXMuZWFzdHVzIiwiZXhwIjoxNjk1MDYyMjY1LCJpc3MiOiJ1cm46bXMuY29nbml0aXZlc2VydmljZXMifQ.og2QncNjHoDTVJwOcujM_-VVCKSehzTEKU-4QqN51kyeoXTogr_dQhIqtSNcDUqsctjHPWMYPvB53lI_mL941g\r\nContent-type: application/ssml+xml\r\nX-Microsoft-OutputFormat: riff-24khz-16bit-mono-pcm\r\nUser-Agent: falamuitoeuespero",
                        //         'method' => 'POST',
                        //         'data' => "{\"text\":\"<speak version='1.0' xml:lang='pt-BR'><voice xml:lang='pt-BR' xml:gender='Female'name='pt-BR-FranciscaNeural'>Olá.</voice></speak>\"}",
                        //     ],
                        // ];

                        // $context = stream_context_create($options);
                        // $result = file_get_contents($urlPOST, false, $context);
                        // if ($result === false) {
                        //     /* Handle error */
                        //     $result = "Deu ruim.";
                        // }


                                                // curl
                        $curl = curl_init();

                        curl_setopt_array($curl, [
                        CURLOPT_URL => "https://eastus.tts.speech.microsoft.com/cognitiveservices/v1",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => "<speak version='1.0' xml:lang='pt-BR'><voice xml:lang='pt-BR' xml:gender='Female' name='pt-BR-FranciscaNeural'>\n        Olá.\n</voice></speak>",
                        CURLOPT_HTTPHEADER => [
                            "Authorization: Bearer eyJhbGciOiJFUzI1NiIsImtpZCI6ImtleTEiLCJ0eXAiOiJKV1QifQ.eyJyZWdpb24iOiJlYXN0dXMiLCJzdWJzY3JpcHRpb24taWQiOiI5MTFkOTMyOWY3Mjg0NjVmYjEzMzQwYjU1ZGJlZTZkYSIsInByb2R1Y3QtaWQiOiJTcGVlY2hTZXJ2aWNlcy5GMCIsImNvZ25pdGl2ZS1zZXJ2aWNlcy1lbmRwb2ludCI6Imh0dHBzOi8vYXBpLmNvZ25pdGl2ZS5taWNyb3NvZnQuY29tL2ludGVybmFsL3YxLjAvIiwiYXp1cmUtcmVzb3VyY2UtaWQiOiIvc3Vic2NyaXB0aW9ucy8wNDU0MGNlYy0zMzRhLTQzMjctYWQyOC0zYzNhM2ExOWZlNTcvcmVzb3VyY2VHcm91cHMvZmFsYW50ZXMvcHJvdmlkZXJzL01pY3Jvc29mdC5Db2duaXRpdmVTZXJ2aWNlcy9hY2NvdW50cy9mYWxhbXVpdG9ldWVzcGVybyIsInNjb3BlIjoic3BlZWNoc2VydmljZXMiLCJhdWQiOiJ1cm46bXMuc3BlZWNoc2VydmljZXMuZWFzdHVzIiwiZXhwIjoxNjk1MDY4NTEyLCJpc3MiOiJ1cm46bXMuY29nbml0aXZlc2VydmljZXMifQ.g6jlxubi_p2dsr8yu5aVkO3ZLnqdGwNU2IHDFglLuWUJD1qOlugI-YGjCmoOLNtvpJnJaDgnRtvEZOjqhLXR0A",
                            "Content-Type: application/ssml+xml",
                            "User-Agent: falamuitoeuespero",
                            "X-Microsoft-OutputFormat: riff-24khz-16bit-mono-pcm"
                        ],
                        ]);

                        // echo curl_exec($curl);
                        // echo curl_getinfo($curl, CURLINFO_HTTP_CODE);

                        $responseTTS = curl_exec($curl);
                        $err = curl_error($curl);

                        // curl_close($curl);

                        // if ($err) {
                        // echo "cURL Error #:" . $err;
                        // } else {
                        // echo $responseTTS;
                        // }

                        // $respostaAPI = var_dump($result);
                        
                        // GET
                        // $respostaAPI = file_get_contents($urlGET);

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
                            'status' => 'Código: '. curl_getinfo($curl, CURLINFO_HTTP_CODE),
                            'sala' => $responseTTS
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
    // echo(json_encode($resposta));
    echo var_dump($response);

?>