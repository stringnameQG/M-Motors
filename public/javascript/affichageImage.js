function changeMainImage(src, element) {
  document.getElementById('main-image').src = src;
  const THUMBS = document.querySelectorAll('.gallery-thumbs img');
        THUMBS.forEach(thumb => thumb.classList.remove('active'));
  element.classList.add('active');
}