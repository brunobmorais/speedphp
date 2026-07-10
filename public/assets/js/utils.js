/**
 * Exporta um elemento como imagem e faz download ou mostra modal (iOS)
 * @param {string} idDiv - ID do elemento a capturar
 */
function exportarComoImagem(idDiv) {
    const elemento = document.getElementById(idDiv);
    if (!elemento) return alert("Elemento não encontrado");

    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

    html2canvas(elemento, {useCORS: true, scale: isIOS ? 2 : 3})
        .then(canvas => {
            const imgData = canvas.toDataURL('image/webp',0.85);

            if (isIOS) {
                mostrarImagemNoModal(imgData);
            } else {
                baixarImagem(imgData);
            }
        })
        .catch(err => {
            console.error("Erro ao gerar imagem:", err);
            alert("Não foi possível gerar a imagem do resultado.");
        });
}

// Mostra a imagem em modal para iOS
function mostrarImagemNoModal(dataUrl) {
    let modal = document.getElementById('imagemModal');
    if (!modal) {
        // Criar modal dinamicamente
        modal = document.createElement('div');
        modal.id = 'imagemModal';
        modal.style.display = 'none';
        modal.style.position = 'fixed';
        modal.style.zIndex = '10000';
        modal.style.left = '0';
        modal.style.top = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0,0,0,0.9)';
        modal.style.backdropFilter = 'blur(5px)';
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.flexDirection = 'column';

        const img = document.createElement('img');
        img.id = 'imagemGerada';
        img.style.maxWidth = '90%';
        img.style.maxHeight = '70%';
        img.style.borderRadius = '10px';
        img.style.objectFit = 'contain';
        modal.appendChild(img);

        const info = document.createElement('p');
        info.innerHTML = 'Toque e segure para salvar a imagem no iPhone/iPad.';
        info.style.color = 'white';
        info.style.textAlign = 'center';
        info.style.marginTop = '15px';
        modal.appendChild(info);

        const btnFechar = document.createElement('button');
        btnFechar.innerText = 'Fechar';
        btnFechar.style.marginTop = '15px';
        btnFechar.style.padding = '10px 20px';
        btnFechar.style.border = 'none';
        btnFechar.style.borderRadius = '5px';
        btnFechar.style.backgroundColor = '#007bff';
        btnFechar.style.color = 'white';
        btnFechar.style.cursor = 'pointer';
        btnFechar.onclick = () => modal.style.display = 'none';
        modal.appendChild(btnFechar);

        document.body.appendChild(modal);
    }

    const imgElement = document.getElementById('imagemGerada');
    imgElement.src = dataUrl;
    modal.style.display = 'flex';
}

// Faz download da imagem
function baixarImagem(dataUrl) {
    const link = document.createElement('a');
    link.href = dataUrl;
    link.download = 'resultado.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Deixa global
window.exportarComoImagem = exportarComoImagem;
