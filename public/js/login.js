document.getElementById('formulari-login').addEventListener('submit', function(e) {
    e.preventDefault(); // Evitem que la pàgina es recarregui sola

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('missatge-error');

    // Amaguem l'error anterior per si de cas
    errorDiv.style.display = 'none';

    // Enviem les dades al controlador a través del Front Controller
    fetch('index.php?action=api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email, password: password }),
        credentials: 'include' // <--- ISOLAT I IMPRESCINDIBLE
    })
    .then(response => {
        // Retornem el JSON tant si és un estat 200 com si és un error (401, 400...)
        return response.json().then(data => {
            if (!response.ok) {
                throw new Error(data.message || "Error en l'autenticació");
            }
            return data;
        });
    })
    .then(data => {
        console.log("Login correcte:", data);
        
        // Com que la sessió s'ha desat correctament a PHP, forcem una redirecció 
        // a l'arrel de l'aplicació. El backend decidirà on enviar-lo segons el rol.
        window.location.href = 'index.php';
    })
    .catch(error => {
        console.error("Error de login:", error);
        // Mostrem el missatge que ens arriba des del teu controlador de PHP
        errorDiv.innerText = error.message;
        errorDiv.style.display = 'block';
    });
});