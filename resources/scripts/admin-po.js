/**
 * Admin .js file
 */

document.addEventListener('DOMContentLoaded', function () {
  const sortableList = document.getElementById('po-sortable-subpages');

  if (!sortableList) {
    return;
  }

  const orderInput = document.getElementById('po-subpage-order');

  const initialItems = Array.from(sortableList.querySelectorAll('li'));
  const initialOrder = initialItems.map(item => item.dataset.id).join(',');

  $(sortableList).sortable({
    axis: 'y',
    opacity: 0.7,
    update: function (event, ui) {
      const items = Array.from(sortableList.querySelectorAll('li'));
      const currentOrder = items.map(item => item.dataset.id).join(',');

      orderInput.value = currentOrder === initialOrder
        ? ''
        : currentOrder;
    }
  }).disableSelection();
});
