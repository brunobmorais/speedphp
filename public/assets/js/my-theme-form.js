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
            window.onpageshow = function (event) {
                if (event.persisted && buttonId) {
                    clickBotaoProgressInativo(buttonId, textButton);
                }
            };

            formElement.submit(); // tudo certo, envia o formulário
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