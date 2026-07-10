function initNacionalidade() {
    const hiddenTipo = document.getElementById('documento-tipo-hidden');
    if (!hiddenTipo) return;

    const tipo = hiddenTipo.value || 'CPF';
    _aplicarTipoDocumento(tipo);
    _aplicarContextoNacionalidade(tipo);
}


function _aplicarContextoNacionalidade(tipo) {
    const isEX = tipo === 'PASSAPORTE';

    const divPais = document.getElementById('div-pais');
    const paisEl = document.getElementById('pais');
    if (divPais) {
        divPais.classList.toggle('d-none', !isEX);
        if (paisEl) {
            if (isEX) {
                paisEl.setAttribute('required', 'required');
            } else {
                paisEl.value = '31';
                paisEl.removeAttribute('required');
            }
        }
    }

    const estadoEl = document.getElementById('estado');
    const cidadeEl = document.getElementById('cidade');
    if (estadoEl) estadoEl.toggleAttribute('required', !isEX);
    if (cidadeEl) cidadeEl.toggleAttribute('required', !isEX);

    const tel = document.getElementById('telefone');
    if (tel) {
        if (isEX) {
            tel.removeAttribute('onkeydown');
            tel.removeAttribute('oninput');
            tel.removeAttribute('minlength');
            tel.removeAttribute('pattern');
            tel.removeAttribute('maxlength');
        } else {
            tel.setAttribute('onkeydown', 'mascara(this,mtel)');
            tel.setAttribute('oninput', 'mascara(this,mtel)');
            tel.setAttribute('minlength', '15');
            tel.setAttribute('maxlength', '15');
            tel.setAttribute('pattern', '\\([0-9]{2}\\) [0-9]{4,5}-[0-9]{4}$');
        }
    }
}

function _aplicarTipoDocumento(tipo) {
    const cpfInput = document.getElementById('cpf');
    const label = document.getElementById('label-documento');
    const hiddenTipo = document.getElementById('documento-tipo-hidden');

    if (hiddenTipo) hiddenTipo.value = tipo;
    if (!cpfInput) return;

    if (tipo === 'PASSAPORTE') {
        if (label) label.textContent = 'Passaporte';
        cpfInput.placeholder = 'Número do passaporte';
        cpfInput.removeAttribute('onkeydown');
        cpfInput.setAttribute('maxlength', '30');
        cpfInput.setAttribute('minlength', '5');
        cpfInput.removeAttribute('pattern');
    } else {
        if (label) label.textContent = 'CPF';
        cpfInput.placeholder = '000.000.000-00';
        cpfInput.setAttribute('onkeydown', 'mascara(this, mcpf)');
        cpfInput.setAttribute('maxlength', '14');
        cpfInput.setAttribute('minlength', '14');
    }
}

// Chamado no keyup do campo de documento
function onDocumentoAutoKeyup() {
    const cpfInput = document.getElementById('cpf');
    const hiddenTipo = document.getElementById('documento-tipo-hidden');
    if (!cpfInput || !hiddenTipo) return;

    // Se o tipo foi definido explicitamente via select, respeita a escolha
    const selectTipo = document.getElementById('select-tipo-documento');
    if (selectTipo) {
        // modo select: só aplica máscara CPF se CPF estiver selecionado
        if (hiddenTipo.value === 'CPF') {
            cpfInput.value = mcpf(cpfInput.value);
            if (typeof buscaPessoaFisicaCPF === 'function') buscaPessoaFisicaCPF();
        }
        return;
    }

    // modo buscaSimples: auto-detecta pelo conteúdo
    const raw = cpfInput.value.replace(/[.\-\s]/g, '');
    const isPassaporte = /[a-zA-Z]/.test(raw);

    if (isPassaporte) {
        hiddenTipo.value = 'PASSAPORTE';
    } else {
        hiddenTipo.value = 'CPF';
        cpfInput.value = mcpf(cpfInput.value);
        if (typeof buscaPessoaFisicaCPF === 'function') buscaPessoaFisicaCPF();
    }
}
