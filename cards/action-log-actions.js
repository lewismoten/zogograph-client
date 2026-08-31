window.ZogographActionLogActionsCard = {
  manifest: Object.freeze({
    id: 'core-action-log-actions',
    locales: ['core-action-log-actions.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-action-log-actions.deleted_file': 'deleted a file',
        'core-action-log-actions.emptied_directory': 'emptied a directory',
        'core-action-log-actions.ran_scan': 'ran a scan',
        'core-action-log-actions.changed_schedule': 'changed the schedule',
        'core-action-log-actions.changed_update_policy':
          'changed the system update policy',
        'core-action-log-actions.sent_test_email': 'sent a test email',
        'core-action-log-actions.cleared_problem_log':
          'cleared the problem log',
        'core-action-log-actions.ignored_diagnostic':
          'ignored a diagnostic finding',
        'core-action-log-actions.restored_diagnostic':
          'restored a diagnostic finding',
        'core-action-log-actions.cleared_audit': 'cleared the audit trail',
        'core-action-log-actions.marked_alerts_read': 'marked alerts read',
        'core-action-log-actions.deleted_alerts': 'deleted alerts',
        'core-action-log-actions.changed_service_watch':
          'changed which services are watched',
        'core-action-log-actions.requested_host_walk':
          'asked the host to walk the disk',
        'core-action-log-actions.checked_capabilities':
          'checked which programs are available',
      }),
      es: Object.freeze({
        'core-action-log-actions.deleted_file': 'eliminó un archivo',
        'core-action-log-actions.emptied_directory': 'vació un directorio',
        'core-action-log-actions.ran_scan': 'ejecutó un análisis',
        'core-action-log-actions.changed_schedule': 'cambió la programación',
        'core-action-log-actions.changed_update_policy':
          'cambió la política de actualización del sistema',
        'core-action-log-actions.sent_test_email': 'envió un correo de prueba',
        'core-action-log-actions.cleared_problem_log':
          'borró el registro de problemas',
        'core-action-log-actions.ignored_diagnostic':
          'ignoró un hallazgo de diagnóstico',
        'core-action-log-actions.restored_diagnostic':
          'restauró un hallazgo de diagnóstico',
        'core-action-log-actions.cleared_audit':
          'borró el registro de auditoría',
        'core-action-log-actions.marked_alerts_read':
          'marcó las alertas como leídas',
        'core-action-log-actions.deleted_alerts': 'eliminó alertas',
        'core-action-log-actions.changed_service_watch':
          'cambió los servicios vigilados',
        'core-action-log-actions.requested_host_walk':
          'pidió al host recorrer el disco',
        'core-action-log-actions.checked_capabilities':
          'comprobó qué programas están disponibles',
      }),
      hi: Object.freeze({
        'core-action-log-actions.deleted_file': 'एक फ़ाइल हटाई',
        'core-action-log-actions.emptied_directory': 'एक डायरेक्टरी खाली की',
        'core-action-log-actions.ran_scan': 'एक स्कैन चलाया',
        'core-action-log-actions.changed_schedule': 'शेड्यूल बदला',
        'core-action-log-actions.changed_update_policy':
          'सिस्टम अपडेट नीति बदली',
        'core-action-log-actions.sent_test_email': 'परीक्षण ईमेल भेजा',
        'core-action-log-actions.cleared_problem_log': 'समस्या लॉग साफ़ किया',
        'core-action-log-actions.ignored_diagnostic':
          'नैदानिक निष्कर्ष अनदेखा किया',
        'core-action-log-actions.restored_diagnostic':
          'नैदानिक निष्कर्ष पुनर्स्थापित किया',
        'core-action-log-actions.cleared_audit': 'ऑडिट ट्रेल साफ़ किया',
        'core-action-log-actions.marked_alerts_read':
          'अलर्ट पढ़े हुए चिह्नित किए',
        'core-action-log-actions.deleted_alerts': 'अलर्ट हटाए',
        'core-action-log-actions.changed_service_watch':
          'निगरानी की जाने वाली सेवाएं बदलीं',
        'core-action-log-actions.requested_host_walk':
          'होस्ट से डिस्क स्कैन कराने को कहा',
        'core-action-log-actions.checked_capabilities':
          'उपलब्ध प्रोग्राम जांचे',
      }),
      ar: Object.freeze({
        'core-action-log-actions.deleted_file': 'حذف ملفًا',
        'core-action-log-actions.emptied_directory': 'أفرغ دليلاً',
        'core-action-log-actions.ran_scan': 'شغّل فحصًا',
        'core-action-log-actions.changed_schedule': 'غيّر الجدول الزمني',
        'core-action-log-actions.changed_update_policy':
          'غيّر سياسة تحديث النظام',
        'core-action-log-actions.sent_test_email':
          'أرسل بريدًا إلكترونيًا تجريبيًا',
        'core-action-log-actions.cleared_problem_log': 'مسح سجل المشكلات',
        'core-action-log-actions.ignored_diagnostic': 'تجاهل نتيجة تشخيصية',
        'core-action-log-actions.restored_diagnostic': 'استعاد نتيجة تشخيصية',
        'core-action-log-actions.cleared_audit': 'مسح سجل التدقيق',
        'core-action-log-actions.marked_alerts_read': 'علّم التنبيهات كمقروءة',
        'core-action-log-actions.deleted_alerts': 'حذف التنبيهات',
        'core-action-log-actions.changed_service_watch':
          'غيّر الخدمات التي تُراقب',
        'core-action-log-actions.requested_host_walk':
          'طلب من المضيف فحص القرص',
        'core-action-log-actions.checked_capabilities':
          'تحقق من البرامج المتاحة',
      }),
      zh: Object.freeze({
        'core-action-log-actions.deleted_file': '删除了一个文件',
        'core-action-log-actions.emptied_directory': '清空了一个目录',
        'core-action-log-actions.ran_scan': '运行了一次扫描',
        'core-action-log-actions.changed_schedule': '更改了计划',
        'core-action-log-actions.changed_update_policy': '更改了系统更新策略',
        'core-action-log-actions.sent_test_email': '发送了测试电子邮件',
        'core-action-log-actions.cleared_problem_log': '清除了问题日志',
        'core-action-log-actions.ignored_diagnostic': '忽略了一项诊断发现',
        'core-action-log-actions.restored_diagnostic': '恢复了一项诊断发现',
        'core-action-log-actions.cleared_audit': '清除了审计记录',
        'core-action-log-actions.marked_alerts_read': '将告警标记为已读',
        'core-action-log-actions.deleted_alerts': '删除了告警',
        'core-action-log-actions.changed_service_watch': '更改了被监控的服务',
        'core-action-log-actions.requested_host_walk': '请求主机扫描磁盘',
        'core-action-log-actions.checked_capabilities': '检查了可用程序',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-action-log-actions.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
