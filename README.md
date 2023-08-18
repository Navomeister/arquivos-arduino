# arquivos-arduino
arquivos que to usando no coiso do arduino


#Parâmetros da API
‌

###1.endpoint
  · Recebe qual serviço deseja acessar;
  · Atualmente disponíveis: “salas”;
  
###2.usuario
  · Recebe o nome de usuário do requerente para autenticação;
  · Registrados atualmente: “arduino1”, “arduino2”;
  · Posteriormente mudar para os Ids únicos de cada arduino no banco;
  
###3.numero_sala
  · Recebe o número da sala que deseja receber informações sobre;
  · Posteriormente mudar para receber por query no banco, para pegar a sala em que aquele ID (do arduino) está cadastrado, assim descartando a necessidade de mudar por código caso troque o arduino de sala;
