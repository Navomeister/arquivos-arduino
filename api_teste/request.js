const form = document.getElementById('formulario');
const responseDiv = document.querySelector('#resposta');

form.addEventListener('submit', function (event) {
    event.preventDefault();

    const usuario = document.getElementById('usuario').value;
    const sala = document.getElementById('numero_sala').value;

    // fazer uma solicitação HTTP para a API
    fetch('http://localhost/api-arduino/api.php/?endpoint=salas&usuario=' + usuario + '&numero_sala=' + sala, {
        // mode: 'no-cors',
        headers: {
        "Content-Type": "application/json"
        }
    })
        .then(Response => Response.json()) // converte a resposta em json
        .then(data => {
            responseDiv.textContent = JSON.stringify(data, null, 2);
        })
        .catch(error => {
            responseDiv.textContent = 'deu erro :/ ' + error.message;
        });
    

});