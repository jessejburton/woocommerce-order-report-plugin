/*
    EVENT LISTENERS
*/

var btn = document.getElementById('submit');
var product = document.getElementById('product');

product.addEventListener('change', () => {
  if (product.options.selectedIndex != -1) {
    btn.disabled = false;
  }
});
