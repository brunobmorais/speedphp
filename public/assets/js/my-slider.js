document.addEventListener('DOMContentLoaded', function() {
  const sliderContainers = document.querySelectorAll('.slider-container');

  sliderContainers.forEach((container) => {
    const slider = container.querySelector('.slider');
    const prevBtn = container.querySelector('.prev-slide');
    const nextBtn = container.querySelector('.next-slide');
    const dotsContainer = container.nextElementSibling;
    const dots = dotsContainer ? dotsContainer.querySelectorAll('.dot') : [];

    if (!slider) return;

    const slideWidth = slider.querySelector('.slide')?.offsetWidth || 0;

    // Função para atualizar dots baseado na posição do scroll
    function updateActiveDot() {
      if (dots.length === 0) return;

      const scrollLeft = slider.scrollLeft;
      let currentIndex = Math.round(scrollLeft / slideWidth);

      // Garantir que o índice não ultrapasse o número de slides
      const slides = slider.querySelectorAll('.slide');
      currentIndex = currentIndex % slides.length;

      dots.forEach((dot, index) => {
        if (index === currentIndex) {
          dot.classList.add('active');
        } else {
          dot.classList.remove('active');
        }
      });
    }

    // Atualizar dots ao fazer scroll (swipe)
    slider.addEventListener('scroll', updateActiveDot);

    // Detectar quando chega no clone e voltar ao início
    slider.addEventListener('scroll', () => {
      const slides = slider.querySelectorAll('.slide');
      const slideWidth = slides[0]?.offsetWidth || 0;
      const cloneSlide = slider.querySelector('.slide-clone');

      if (cloneSlide && slider.scrollLeft >= (slides.length - 1) * slideWidth) {
        // Jump sem animação para o primeiro slide
        setTimeout(() => {
          slider.scrollLeft = 0;
        }, 100);
      }
    });

    // Botão anterior
    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        slider.scrollBy({
          left: -slideWidth,
          behavior: 'smooth'
        });
      });
    }

    // Botão próximo
    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        slider.scrollBy({
          left: slideWidth,
          behavior: 'smooth'
        });
      });
    }

    // Dots clicáveis
    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        slider.scrollTo({
          left: index * slideWidth,
          behavior: 'smooth'
        });
      });
    });

    // Inicializar primeiro dot como ativo
    if (dots.length > 0) {
      dots[0].classList.add('active');
    }

    // Auto-scroll temporizado (5 segundos)
    let autoScrollInterval;
    let currentSlideIndex = 0;

    function startAutoScroll() {
      autoScrollInterval = setInterval(() => {
        const slides = slider.querySelectorAll('.slide:not(.slide-clone)');
        const totalSlides = slides.length;

        currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
        slider.scrollTo({
          left: currentSlideIndex * slideWidth,
          behavior: 'smooth'
        });
      }, 5000); // Muda a cada 5 segundos
    }

    function resetAutoScroll() {
      clearInterval(autoScrollInterval);
      startAutoScroll();
    }

    // Iniciar auto-scroll
    startAutoScroll();

    // Pausar auto-scroll quando usuário interage
    slider.addEventListener('scroll', () => {
      clearInterval(autoScrollInterval);
      const scrollLeft = slider.scrollLeft;
      const slides = slider.querySelectorAll('.slide:not(.slide-clone)');
      const totalSlides = slides.length;
      currentSlideIndex = Math.round(scrollLeft / slideWidth) % totalSlides;
    });

    // Retomar auto-scroll após 10 segundos de inatividade
    let inactivityTimeout;
    function setupInactivityTimer() {
      clearTimeout(inactivityTimeout);
      inactivityTimeout = setTimeout(() => {
        resetAutoScroll();
      }, 10000);
    }

    slider.addEventListener('scroll', setupInactivityTimer);
    prevBtn?.addEventListener('click', setupInactivityTimer);
    nextBtn?.addEventListener('click', setupInactivityTimer);
    dots.forEach(dot => dot.addEventListener('click', setupInactivityTimer));
  });
});
