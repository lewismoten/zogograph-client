/* The single browser boundary for reviewed card definitions. Cards register
 * themselves here instead of adding another feature-specific window global. */
(() => {
  const entries = new Map();

  function validManifest(manifest) {
    const id = manifest?.id;
    const prefix = `${id}.`;
    return (
      typeof id === 'string' &&
      /^core-[a-z][a-z0-9-]*$/.test(id) &&
      Array.isArray(manifest.locales) &&
      manifest.locales.length > 0 &&
      manifest.locales.every((locale) => locale === prefix) &&
      Array.isArray(manifest.helpers) &&
      manifest.helpers.every(
        (helper, index) =>
          typeof helper === 'string' && manifest.helpers.indexOf(helper) === index,
      ) &&
      manifest.catalogs &&
      !Array.isArray(manifest.catalogs) &&
      typeof manifest.catalogs === 'object'
    );
  }

  function register(card) {
    const id = card?.manifest?.id;
    if (
      !validManifest(card?.manifest) ||
      typeof card.render !== 'function' ||
      entries.has(id)
    ) {
      throw new TypeError('Core card registration is invalid.');
    }
    entries.set(id, card);
    return card;
  }

  const cards = Object.freeze({
    register,
    get: (id) => entries.get(id),
    has: (id) => entries.has(id),
    all: () => Array.from(entries.values()),
  });
  const product = window.Zogograph || {};
  if (product.cards) throw new TypeError('Core card registry already exists.');
  product.cards = cards;
  window.Zogograph = product;
})();
