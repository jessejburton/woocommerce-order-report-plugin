/*
    EVENT LISTENERS
*/

var btn = document.getElementById('submit');
var product = document.getElementById('product');

// Enable on load if items are already selected
if (product.options.selectedIndex != -1) {
  btn.disabled = false;
}

// Enable on select if form has not been submitted
product.addEventListener('change', () => {
  if (product.options.selectedIndex != -1) {
    btn.disabled = false;
  }
});
