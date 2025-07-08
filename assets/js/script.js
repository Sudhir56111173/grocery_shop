// Navbar shadow on scroll
window.addEventListener('scroll', function() {
  const nav = document.querySelector('.navbar');
  if (window.scrollY > 10) {
    nav.classList.add('shadow');
  } else {
    nav.classList.remove('shadow');
  }
});

// Smooth scroll for anchor links (if any)
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});
