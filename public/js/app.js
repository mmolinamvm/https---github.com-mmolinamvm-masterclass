var player;              
var intervalEscolta;      
var llistatPreguntes = [];
var preguntesRespondides = []; 
var preguntaActual = null;
var ultimTempsValid = 0; 

function carregarPreguntesDelBackend() {
    fetch('../api/get_preguntes.php') // Manté el camí relacional anterior temporalment
        .then(response => {
            if (!response.ok) throw new Error("No s'han pogut carregar les preguntes");
            return response.json();
        })
        .then(data => {
            llistatPreguntes = data;
            console.log("Preguntes carregades:", llistatPreguntes);
            inicialitzarYouTubeAPI();
        })
        .catch(error => {
            console.error("Error carregant l'API:", error);
        });
}

function inicialitzarYouTubeAPI() {
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
}

window.onload = function() {
    carregarPreguntesDelBackend();
};

function onYouTubeIframeAPIReady() {
    player = new YT.Player('reproductor', {
        height: '360',
        width: '640',
        videoId: 'Oe2tzG4vI0o', 
        playerVars: { 'controls': 1, 'rel': 0 },
        events: { 'onStateChange': onPlayerStateChange }
    });
}

function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.PLAYING) {
        intervalEscolta = setInterval(comprovarTemps, 500);
    } else {
        clearInterval(intervalEscolta);
    }
}

function comprovarTemps() {
    var tempsActual = player.getCurrentTime();
    var segonActual = Math.floor(tempsActual);
    
    llistatPreguntes.forEach(function(p) {
        if (!preguntesRespondides.includes(p.id) && ultimTempsValid < p.segon && segonActual > p.segon) {
            player.seekTo(p.segon); 
            segonActual = p.segon;
        }
    });
    
    ultimTempsValid = tempsActual;

    var trobada = llistatPreguntes.find(function(p) {
        return p.segon === segonActual && !preguntesRespondides.includes(p.id);
    });

    if (trobada && document.getElementById('modal-pregunta').style.display !== 'block') {
        lllançarPregunta(trobada);
    }
}

function llançarPregunta(pregunta) {
    clearInterval(intervalEscolta); 
    player.pauseVideo();            
    preguntaActual = pregunta; 

    document.getElementById('enunciat').innerText = pregunta.text;
    var contenidorForm = document.getElementById('contenidor-formulari');
    contenidorForm.innerHTML = ""; 

    if (pregunta.tipus === "text") {
        contenidorForm.innerHTML = '<input type="text" id="resposta-text" autofocus placeholder="Escriu la teva resposta aquí...">';
    } else if (pregunta.tipus === "single") {
        pregunta.opcions.forEach(function(opcio) {
            contenidorForm.innerHTML += `
                <label class="opcio-bloc">
                    <input type="radio" name="opcio-single" value="${opcio.id}"> <span>${opcio.text_opcio}</span>
                </label>`;
        });
    } else if (pregunta.tipus === "multiple") {
        pregunta.opcions.forEach(function(opcio) {
            contenidorForm.innerHTML += `
                <label class="opcio-bloc">
                    <input type="checkbox" name="opcio-multiple" value="${opcio.id}"> <span>${opcio.text_opcio}</span>
                </label>`;
        });
    }

    document.getElementById('modal-pregunta').style.display = 'block';
    document.getElementById('pantalla-fosca').style.display = 'block';
}

function continuarVideo() {
    var respostaGuardar = null;

    if (preguntaActual.tipus === "text") {
        var textInput = document.getElementById('resposta-text').value;
        if (textInput.trim() === "") return alert("Respon la pregunta de text.");
        respostaGuardar = textInput;
    } else if (preguntaActual.tipus === "single") {
        var seleccionat = document.querySelector('input[name="opcio-single"]:checked');
        if (!seleccionat) return alert("Selecciona una opció.");
        respostaGuardar = parseInt(seleccionat.value);
    } else if (preguntaActual.tipus === "multiple") {
        var seleccionats = document.querySelectorAll('input[name="opcio-multiple"]:checked');
        if (seleccionats.length === 0) return alert("Selecciona com a mínim una opció.");
        
        respostaGuardar = [];
        seleccionats.forEach(function(cb) {
            respostaGuardar.push(parseInt(cb.value));
        });
    }

    var dadesAEnviar = {
        pregunta_id: preguntaActual.id,
        tipus: preguntaActual.tipus,
        resposta: respostaGuardar
    };

    fetch('../api/post_resposta.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dadesAEnviar)
    })
    .then(response => response.json())
    .then(data => {
        console.log("Èxit de resposta desar:", data);
        preguntesRespondides.push(preguntaActual.id);
        document.getElementById('modal-pregunta').style.display = 'none';
        document.getElementById('pantalla-fosca').style.display = 'none';
        player.playVideo();
    })
    .catch(error => {
        console.error("Error xarxa:", error);
        alert("Error desant dades. Revisa el servidor backend.");
    });
}