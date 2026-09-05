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

  function mount(container, packageId, cardId) {
    if (!(container instanceof Element)) return null;
    const existing = Array.from(container.children).find(
      (node) =>
        node instanceof HTMLElement &&
        node.dataset.package === packageId &&
        node.dataset.card === cardId,
    );
    if (existing) return existing;
    const node = document.createElement('div');
    node.className = 'card-mount';
    node.dataset.package = packageId;
    node.dataset.card = cardId;
    container.appendChild(node);
    return node;
  }

  function append(container, node) {
    if (!(container instanceof Node) || !(node instanceof Node)) return false;
    container.appendChild(node);
    return true;
  }

  product.ui = Object.freeze({ append, empty, mount });
})();
