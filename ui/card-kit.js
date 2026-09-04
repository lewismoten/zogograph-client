/* Shared, safe card primitives. Feature cards receive these through their
 * helper context rather than duplicating common DOM and state treatment. */
(() => {
  const product = window.Zogograph;
  if (!product?.cards || product.ui)
    throw new TypeError('Core card UI kit cannot be initialized.');

  function empty(message) {
    const node = document.createElement('p');
    node.className = 'muted';
    node.textContent = String(message ?? '');
    return node;
  }

  product.ui = Object.freeze({ empty });
})();
