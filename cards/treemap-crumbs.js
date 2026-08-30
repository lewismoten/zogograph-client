/* Core card package: treemap path navigation. */
window.ZogographTreemapCrumbsCard = {
  manifest: Object.freeze({
    id: 'core-treemap-crumbs',
    locales: ['core-treemap-crumbs.'],
    helpers: ['target', 'up', 'open', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-treemap-crumbs.watched': 'watched directory' }),
      es: Object.freeze({
        'core-treemap-crumbs.watched': 'directorio vigilado',
      }),
      hi: Object.freeze({
        'core-treemap-crumbs.watched': 'देखी गई डायरेक्टरी',
      }),
      ar: Object.freeze({ 'core-treemap-crumbs.watched': 'الدليل المراقب' }),
      zh: Object.freeze({ 'core-treemap-crumbs.watched': '监视的目录' }),
    }),
  }),
  render(input, helpers) {
    const { target, up, open, i18n } = helpers;
    const { level, source } = input;
    if (!target) return;
    if (!level) {
      target.replaceChildren();
      return;
    }
    const root = level.root || '/';
    const rest = level.path.startsWith(root)
      ? level.path.slice(root.length)
      : level.path;
    const names = rest.split('/').filter(Boolean);
    const fragment = document.createDocumentFragment();
    const add = (label, path, current) => {
      const node = document.createElement(current ? 'span' : 'button');
      node.textContent = label;
      if (!current) {
        node.className = 'link';
        node.addEventListener('click', () => open(path));
      }
      fragment.appendChild(node);
    };
    add(
      source === 'host'
        ? '/'
        : i18n.t('core-treemap-crumbs.watched', {}, 'watched directory'),
      root,
      names.length === 0,
    );
    let walk = root;
    names.forEach((name, index) => {
      walk = walk.replace(/\/$/, '') + '/' + name;
      const separator = document.createElement('span');
      separator.className = 'sep';
      separator.textContent = '/';
      fragment.appendChild(separator);
      add(name, walk, index === names.length - 1);
    });
    target.replaceChildren(fragment);
    if (up) up.disabled = !level.parent;
  },
};
