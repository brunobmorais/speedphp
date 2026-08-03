// VALIDACAO BOOTSTRAP
(() => {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})();
// FIM VALIDACAO BOOTSTRAP

// FORM SUBMIT
document.querySelectorAll("form").forEach((formElement) => {
    formElement.addEventListener("submit", (event) => {
        event.preventDefault();

        const submitter = event.submitter;
        const buttonId = submitter?.id || null;
        const formId = formElement.id;
        const textButton = submitter?.innerHTML || "";

        if (buttonId) clickBotaoProgressAtivo(buttonId);

        // Modificação aqui - passamos o elemento do formulário diretamente
        // ao invés de tentar buscá-lo pelo ID
        validaFormElement(formElement);

        if (formElement.checkValidity()) {
            const isMultipart = (formElement.getAttribute('enctype') || '').toLowerCase() === 'multipart/form-data';

            if (isMultipart) {
                // form.submit() nativo pode sair com o corpo vazio (Content-Length: 0) no
                // Safari/iOS após escolher um arquivo pela galeria/câmera. Montar o FormData
                // em JS e enviar via fetch() evita esse bug.
                const method = (formElement.getAttribute('method') || 'POST').toUpperCase();
                const action = formElement.getAttribute('action') || window.location.href;
                const formData = new FormData(formElement);

                fetch(action, {
                    method: method,
                    body: formData,
                    credentials: 'same-origin',
                    headers: { 'X-Fetch-Redirect': '1' }
                }).then((response) => {
                    // O servidor responde com 200 + header X-Redirect-To (em vez de um
                    // redirect HTTP de verdade) para o fetch não consumir a mensagem flash
                    // da sessão antes da navegação real acontecer.
                    window.location.href = response.headers.get('X-Redirect-To') || response.url;
                }).catch(() => {
                    if (buttonId) clickBotaoProgressInativo(buttonId, textButton);
                    alertError("Falha ao enviar o formulário. Verifique sua conexão e tente novamente.");
                });
            } else {
                window.onpageshow = function (event) {
                    if (event.persisted && buttonId) {
                        clickBotaoProgressInativo(buttonId, textButton);
                    }
                };

                formElement.submit(); // tudo certo, envia o formulário
            }
        } else {
            if (buttonId) clickBotaoProgressInativo(buttonId, textButton);
            alertError("Preencha todos os campos corretamente!");
        }
    });
});
// FIM FORM SUBMIT

// Função modificada para receber o elemento diretamente em vez do ID
function validaFormElement(formElement) {
    if (formElement.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
    }
    formElement.classList.add('was-validated');
}

async function formToMap(formData){
    const value = Object.fromEntries(formData.entries());
    return value;
}


