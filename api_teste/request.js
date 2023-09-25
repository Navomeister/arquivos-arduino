const form = document.getElementById('formulario');
const responseDiv = document.querySelector('#resposta');
const ctx = new AudioContext();
let audio;


form.addEventListener('submit', function (event) {
    event.preventDefault();

    const usuario = document.getElementById('usuario').value;
    const endpoint = document.getElementById('numero_sala').value;

    if (endpoint != "salas") {
        // fazer uma solicitação HTTP para a API
    fetch('https://apenasumtestezinho.000webhostapp.com/api/api.php/?endpoint=salas&usuario=' + usuario + '&endpoint=' + endpoint, {
        // mode: 'no-cors',
        headers: {
        "Content-Type": "application/json"
        }
    })
        .then(Response => Response.json()) // converte a resposta em json
        .then(data => {
            responseDiv.textContent = toString(data);
        })
        .catch(error => {
            responseDiv.textContent = 'deu erro :/ ' + error.message;
        });
    } else {
        // fazer uma solicitação HTTP para a API
        fetch('https://apenasumtestezinho.000webhostapp.com/api/api.php/?endpoint=salas&usuario=' + usuario + '&endpoint=' + endpoint, {
            // mode: 'no-cors',
            headers: {
            "Content-Type": "application/json"
            }
        })
            .then(data => data.arrayBuffer())
            .then(arrayBuffer => ctx.decodeAudioData(arrayBuffer))
            .then(decodedAudio => {
                audio = decodedAudio;
                responseDiv.innerHTML = '<img src="images/play.svg" alt="" id="play">';
                const play = document.getElementById("play");
                play.addEventListener("mousedown", playback);
            });
    }
});

function playback() {
    const playSound = ctx.createBufferSource();
    playSound.buffer = audio;
    playSound.connect(ctx.destination);
    playSound.start(ctx.currentTime);
  }
