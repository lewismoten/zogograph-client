/* Core card package: the shell supplies only the host-action capabilities it needs. */
window.ZogographHostWalkControl = {
  manifest: Object.freeze({
    id: 'core-host-walk-control',
    locales: ['core-host-walk-control.'],
    helpers: ['target', 'ago', 'post', 'notice', 'refresh', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-walk-control.pending':
          "A walk was requested {{ago}} and is waiting for the next host collection. It runs on the collector's schedule — every five minutes with the suggested crontab line.",
        'core-host-walk-control.walk': 'Walk the disk',
        'core-host-walk-control.walk_again': 'Walk the disk again',
        'core-host-walk-control.requested':
          'Requested. The host collector will walk the disk on its next run.',
      }),
      es: Object.freeze({
        'core-host-walk-control.pending':
          'Se solicitó un recorrido {{ago}} y está esperando la siguiente recopilación del host. Se ejecuta según el horario del recopilador: cada cinco minutos con la línea de crontab sugerida.',
        'core-host-walk-control.walk': 'Recorrer el disco',
        'core-host-walk-control.walk_again': 'Recorrer el disco otra vez',
        'core-host-walk-control.requested':
          'Solicitado. El recopilador del host recorrerá el disco en su próxima ejecución.',
      }),
      hi: Object.freeze({
        'core-host-walk-control.pending':
          '{{ago}} एक वॉक का अनुरोध किया गया था और वह अगले होस्ट संग्रह की प्रतीक्षा में है। यह कलेक्टर के समय-सारणी पर चलता है — सुझाई गई crontab लाइन के साथ हर पाँच मिनट में।',
        'core-host-walk-control.walk': 'डिस्क वॉक करें',
        'core-host-walk-control.walk_again': 'डिस्क फिर से वॉक करें',
        'core-host-walk-control.requested':
          'अनुरोध किया गया। होस्ट कलेक्टर अपने अगले रन में डिस्क वॉक करेगा।',
      }),
      ar: Object.freeze({
        'core-host-walk-control.pending':
          'طُلبت جولة منذ {{ago}} وهي تنتظر جمع المضيف التالي. تعمل وفق جدول الجامع — كل خمس دقائق مع سطر crontab المقترح.',
        'core-host-walk-control.walk': 'افحص القرص',
        'core-host-walk-control.walk_again': 'افحص القرص مرة أخرى',
        'core-host-walk-control.requested':
          'تم الطلب. سيفحص جامع المضيف القرص في تشغيله التالي.',
      }),
      zh: Object.freeze({
        'core-host-walk-control.pending':
          '{{ago}}前已请求一次遍历，正在等待下一次主机采集。它会按采集器的计划运行——使用建议的 crontab 行时每五分钟一次。',
        'core-host-walk-control.walk': '遍历磁盘',
        'core-host-walk-control.walk_again': '再次遍历磁盘',
        'core-host-walk-control.requested':
          '已请求。主机采集器将在下次运行时遍历磁盘。',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, ago, post, notice, refresh, i18n } = helpers;
    if (!target) return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const box = document.createElement('p');
    if (data.pending) {
      box.className = 'muted small';
      box.textContent = t(
        'core-host-walk-control.pending',
        { ago: ago(data.requested_at) },
        "A walk was requested {{ago}} and is waiting for the next host collection. It runs on the collector's schedule — every five minutes with the suggested crontab line.",
      );
      target.replaceChildren(box);
      return;
    }
    const button = document.createElement('button');
    button.textContent = data.taken_at
      ? t('core-host-walk-control.walk_again', {}, 'Walk the disk again')
      : t('core-host-walk-control.walk', {}, 'Walk the disk');
    button.addEventListener('click', async () => {
      button.disabled = true;
      try {
        await post('request-host-walk');
        notice(
          t(
            'core-host-walk-control.requested',
            {},
            'Requested. The host collector will walk the disk on its next run.',
          ),
        );
        await refresh();
      } catch (error) {
        notice(String(error.message || error));
        button.disabled = false;
      }
    });
    box.appendChild(button);
    target.replaceChildren(box);
  },
};
