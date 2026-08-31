/* Core card package: CPU-state summaries share the shell's chart and labels. */
window.ZogographCpuTimeCard = {
  manifest: Object.freeze({
    id: 'core-cpu-time',
    locales: ['core-cpu-time.'],
    helpers: [
      'setCard',
      'note',
      'tableTarget',
      'renderTable',
      'bandOf',
      'emptyMessage',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-time.title': 'Where the time goes',
        'core-cpu-time.help':
          'A processor is never simply ‘busy’. Every jiffy it spends is charged to one of eight states, and which one decides what to do next — work in userland is the machine earning its keep, time in the kernel is a syscall-heavy workload, iowait is a disk problem wearing a CPU costume, and steal is the hypervisor and not you at all. The single busy percentage everywhere else on this dashboard is all of these added up and is the reason the question usually gets asked twice.',
        'core-cpu-time.meaning_title': 'What each of them means',
        'core-cpu-time.average_over_range':
          '{{percent}}% on average over this range',
        'core-cpu-time.state': 'State',
        'core-cpu-time.in_proc_stat': 'In /proc/stat',
        'core-cpu-time.now': 'Now',
        'core-cpu-time.what_it_means': 'What it means',
        'core-cpu-time.cores_unknown': 'cores unknown',
        'core-cpu-time.state_label.steal': 'Stolen by the host',
        'core-cpu-time.state_label.iowait': 'Waiting for storage',
        'core-cpu-time.state_label.softirq': 'Deferred interrupt work',
        'core-cpu-time.state_label.irq': 'Hardware interrupts',
        'core-cpu-time.state_label.system': 'Kernel, for a process',
        'core-cpu-time.state_label.user': 'Userland',
        'core-cpu-time.state_label.nice': 'Userland, low priority',
        'core-cpu-time.state_label.idle': 'Idle',
        'core-cpu-time.state_meaning.steal':
          'Time this machine was ready to run and the hypervisor gave the physical core to somebody else. Not work being done here, and not something any change on this machine can reduce. On shared or virtualised hosting this is the figure that explains ‘it is slow and nothing is running’.',
        'core-cpu-time.state_meaning.iowait':
          'The core had nothing to run and something was blocked on a disk. This is idle time, not CPU being consumed — the processor is the victim here rather than the problem. High iowait means go and look at the disks; adding CPU would change nothing.',
        'core-cpu-time.state_meaning.softirq':
          'The second half of interrupt handling, done outside the interrupt itself. On a server this is mostly the network stack. A whole core of it is a finding: it usually means packet volume rather than anything a process is doing.',
        'core-cpu-time.state_meaning.irq':
          'Servicing devices. Ordinarily a fraction of a percent; anything visible on this chart points at a device or a driver.',
        'core-cpu-time.state_meaning.system':
          'Time in the kernel on some process’s behalf: system calls, page faults, memory being mapped. More of this than userland time means the work is syscall-heavy — lots of small reads and writes, lots of process creation — rather than computation.',
        'core-cpu-time.state_meaning.user':
          'Ordinary work at normal priority: the thing you actually asked the machine to do. A machine busy in userland and nothing else is a machine working, which may be entirely correct.',
        'core-cpu-time.state_meaning.nice':
          'The same, at lowered priority — backups, batch jobs, anything run under nice. It yields to everything else, so a machine that is busy only here is not a machine anybody is waiting on.',
        'core-cpu-time.state_meaning.idle':
          'Nothing to run, and nothing waiting on a disk either.',
        'core-cpu-time.cores_count':
          '{{count}} core{{count, plural, one {} other {s}}}',
        'core-cpu-time.time_verdict_spread':
          'Percentages are of all {{cores}} together, so 100% would be the whole machine occupied.',
        'core-cpu-time.time_verdict_idle':
          'The machine is idle — {{busy}}% busy over the last reading. {{spread}}',
        'core-cpu-time.time_verdict_busy':
          '{{busy}}% busy, and most of it is {{state}} ({{percent}}%){{next}}. {{spread}}',
        'core-cpu-time.time_verdict_second': ', then {{state}} ({{percent}}%)',
        'core-cpu-time.time_verdict_state.user': 'ordinary work in userland',
        'core-cpu-time.time_verdict_state.nice': 'low-priority work',
        'core-cpu-time.time_verdict_state.system':
          'kernel work on behalf of processes',
        'core-cpu-time.time_verdict_state.iowait': 'waiting for storage',
        'core-cpu-time.time_verdict_state.steal':
          'time the hypervisor took away',
        'core-cpu-time.time_verdict_state.softirq': 'deferred interrupt work',
        'core-cpu-time.time_verdict_state.irq': 'hardware interrupts',
      }),
      es: Object.freeze({
        'core-cpu-time.title': 'Dónde se va el tiempo',
        'core-cpu-time.help':
          'Un procesador nunca está simplemente «ocupado». Cada jiffy que consume se asigna a uno de ocho estados, y cuál sea determina qué hacer después: el trabajo en espacio de usuario hace que la máquina produzca, el tiempo en el kernel es una carga intensiva en llamadas al sistema, iowait es un problema de disco disfrazado de CPU y steal pertenece al hipervisor, no a usted. El único porcentaje de ocupación del resto del panel es la suma de todos ellos, por eso la pregunta suele hacerse dos veces.',
        'core-cpu-time.meaning_title': 'Qué significa cada uno',
        'core-cpu-time.average_over_range':
          '{{percent}}% de promedio en este intervalo',
        'core-cpu-time.state': 'Estado',
        'core-cpu-time.in_proc_stat': 'En /proc/stat',
        'core-cpu-time.now': 'Ahora',
        'core-cpu-time.what_it_means': 'Qué significa',
        'core-cpu-time.cores_unknown': 'núcleos desconocidos',
        'core-cpu-time.state_label.steal': 'Robado por el host',
        'core-cpu-time.state_label.iowait': 'Esperando almacenamiento',
        'core-cpu-time.state_label.softirq': 'Trabajo de interrupción diferida',
        'core-cpu-time.state_label.irq': 'Interrupciones de hardware',
        'core-cpu-time.state_label.system': 'Kernel, para un proceso',
        'core-cpu-time.state_label.user': 'Espacio de usuario',
        'core-cpu-time.state_label.nice': 'Espacio de usuario, baja prioridad',
        'core-cpu-time.state_label.idle': 'Inactivo',
        'core-cpu-time.state_meaning.steal':
          'Tiempo en que esta máquina estaba lista para ejecutarse y el hipervisor entregó el núcleo físico a otra persona. No es trabajo realizado aquí ni algo que un cambio en esta máquina pueda reducir. En alojamiento compartido o virtualizado, esta cifra explica «va lento y no se está ejecutando nada».',
        'core-cpu-time.state_meaning.iowait':
          'El núcleo no tenía nada que ejecutar y algo estaba bloqueado en un disco. Es tiempo inactivo, no CPU consumida: aquí el procesador es la víctima, no el problema. Un iowait alto indica que debe revisar los discos; añadir CPU no cambiaría nada.',
        'core-cpu-time.state_meaning.softirq':
          'La segunda mitad de la gestión de interrupciones, realizada fuera de la propia interrupción. En un servidor suele ser la pila de red. Un núcleo completo aquí es un hallazgo: normalmente significa volumen de paquetes, no trabajo de un proceso.',
        'core-cpu-time.state_meaning.irq':
          'Atención a dispositivos. Normalmente es una fracción de un porcentaje; cualquier valor visible en este gráfico apunta a un dispositivo o controlador.',
        'core-cpu-time.state_meaning.system':
          'Tiempo en el kernel en nombre de algún proceso: llamadas al sistema, fallos de página, asignación de memoria. Más de esto que de tiempo de usuario significa un trabajo intensivo en llamadas al sistema — muchas lecturas y escrituras pequeñas, mucha creación de procesos — y no cálculo.',
        'core-cpu-time.state_meaning.user':
          'Trabajo normal con prioridad normal: lo que realmente pidió a la máquina que hiciera. Una máquina ocupada en espacio de usuario y nada más está trabajando, y puede ser completamente correcto.',
        'core-cpu-time.state_meaning.nice':
          'Lo mismo, con prioridad reducida: copias de seguridad, trabajos por lotes o cualquier cosa ejecutada con nice. Cede ante todo lo demás, así que una máquina ocupada solo aquí no mantiene a nadie esperando.',
        'core-cpu-time.state_meaning.idle':
          'Nada que ejecutar y nada esperando en un disco.',
        'core-cpu-time.cores_count':
          '{{count}} núcleo{{count, plural, one {} other {s}}}',
        'core-cpu-time.time_verdict_spread':
          'Los porcentajes son de {{cores}} en conjunto; 100% ocuparía toda la máquina.',
        'core-cpu-time.time_verdict_idle':
          'La máquina está inactiva: {{busy}}% ocupada en la última lectura. {{spread}}',
        'core-cpu-time.time_verdict_busy':
          '{{busy}}% ocupada, y la mayor parte es {{state}} ({{percent}}%){{next}}. {{spread}}',
        'core-cpu-time.time_verdict_second':
          ', después {{state}} ({{percent}}%)',
        'core-cpu-time.time_verdict_state.user': 'trabajo normal de usuario',
        'core-cpu-time.time_verdict_state.nice': 'trabajo de baja prioridad',
        'core-cpu-time.time_verdict_state.system':
          'trabajo del núcleo para procesos',
        'core-cpu-time.time_verdict_state.iowait': 'esperando almacenamiento',
        'core-cpu-time.time_verdict_state.steal':
          'tiempo tomado por el hipervisor',
        'core-cpu-time.time_verdict_state.softirq':
          'trabajo de interrupción diferida',
        'core-cpu-time.time_verdict_state.irq': 'interrupciones de hardware',
      }),
      hi: Object.freeze({
        'core-cpu-time.title': 'समय कहाँ जाता है',
        'core-cpu-time.help':
          'प्रोसेसर कभी केवल ‘व्यस्त’ नहीं होता। उसका हर jiffy आठ अवस्थाओं में से एक को दिया जाता है, और वही अगला कदम तय करती है—यूज़रस्पेस का काम मशीन का उपयोगी काम है, कर्नेल समय syscall-भारी कार्यभार है, iowait CPU के वेश में डिस्क समस्या है, और steal हाइपरवाइज़र का है, आपका बिल्कुल नहीं। डैशबोर्ड में हर जगह का एकल व्यस्त प्रतिशत इन सबका योग है, इसलिए सवाल अक्सर दो बार पूछा जाता है।',
        'core-cpu-time.meaning_title': 'हर एक का क्या अर्थ है',
        'core-cpu-time.average_over_range': 'इस अवधि में औसतन {{percent}}%',
        'core-cpu-time.state': 'स्थिति',
        'core-cpu-time.in_proc_stat': '/proc/stat में',
        'core-cpu-time.now': 'अभी',
        'core-cpu-time.what_it_means': 'इसका अर्थ',
        'core-cpu-time.cores_unknown': 'कोर अज्ञात',
        'core-cpu-time.state_label.steal': 'होस्ट द्वारा लिया गया',
        'core-cpu-time.state_label.iowait': 'स्टोरेज की प्रतीक्षा',
        'core-cpu-time.state_label.softirq': 'स्थगित इंटरप्ट कार्य',
        'core-cpu-time.state_label.irq': 'हार्डवेयर इंटरप्ट',
        'core-cpu-time.state_label.system': 'प्रोसेस के लिए कर्नेल',
        'core-cpu-time.state_label.user': 'यूज़र स्पेस',
        'core-cpu-time.state_label.nice': 'यूज़र स्पेस, निम्न प्राथमिकता',
        'core-cpu-time.state_label.idle': 'निष्क्रिय',
        'core-cpu-time.state_meaning.steal':
          'यह वह समय है जब मशीन चलने के लिए तैयार थी लेकिन हाइपरवाइज़र ने भौतिक कोर किसी और को दे दिया। यह यहाँ हो रहा काम नहीं है और न ही ऐसा कुछ है जिसे इस मशीन में बदलाव करके कम किया जा सकता है। साझा या वर्चुअल होस्टिंग में यही आंकड़ा बताता है कि «धीमा है और कुछ चल नहीं रहा»।',
        'core-cpu-time.state_meaning.iowait':
          'कोर के पास चलाने के लिए कुछ नहीं था और कोई चीज़ डिस्क पर रुकी हुई थी। यह निष्क्रिय समय है, CPU खपत नहीं—यहाँ प्रोसेसर समस्या नहीं बल्कि पीड़ित है। iowait अधिक हो तो डिस्क देखें; CPU जोड़ने से कुछ नहीं बदलेगा।',
        'core-cpu-time.state_meaning.softirq':
          'इंटरप्ट हैंडलिंग का दूसरा भाग, जो स्वयं इंटरप्ट के बाहर किया जाता है। सर्वर पर यह अधिकतर नेटवर्क स्टैक होता है। इसमें एक पूरा कोर होना एक संकेत है: यह आमतौर पर पैकेट की मात्रा दर्शाता है, किसी प्रक्रिया का काम नहीं।',
        'core-cpu-time.state_meaning.irq':
          'उपकरणों की सेवा। सामान्यतः प्रतिशत का एक अंश; इस चार्ट में दिखने वाला कोई भी मान किसी उपकरण या ड्राइवर की ओर संकेत करता है।',
        'core-cpu-time.state_meaning.system':
          'किसी प्रक्रिया की ओर से कर्नेल में बिताया समय: सिस्टम कॉल, पेज फॉल्ट, मेमोरी मैप करना। यूज़र स्पेस समय से अधिक होने का अर्थ है कि कार्य सिस्टम कॉल-प्रधान है—बहुत छोटे पढ़ने-लिखने और प्रक्रियाएँ बनाने वाला—न कि गणना-प्रधान।',
        'core-cpu-time.state_meaning.user':
          'सामान्य प्राथमिकता पर सामान्य काम: जो आपने वास्तव में मशीन से करवाने को कहा। केवल यूज़र स्पेस में व्यस्त मशीन काम कर रही है, और यह पूरी तरह सही हो सकता है।',
        'core-cpu-time.state_meaning.nice':
          'वही काम कम प्राथमिकता पर—बैकअप, बैच जॉब या nice के अंतर्गत चलने वाला कुछ भी। यह बाकी सबको रास्ता देता है, इसलिए केवल इसमें व्यस्त मशीन किसी को प्रतीक्षा नहीं करा रही है।',
        'core-cpu-time.state_meaning.idle':
          'चलाने के लिए कुछ नहीं और डिस्क पर भी कुछ प्रतीक्षा नहीं कर रहा।',
        'core-cpu-time.cores_count': '{{count}} कोर',
        'core-cpu-time.time_verdict_spread':
          'प्रतिशत सभी {{cores}} का कुल है, इसलिए 100% पूरी मशीन व्यस्त है।',
        'core-cpu-time.time_verdict_idle':
          'मशीन निष्क्रिय है — पिछली रीडिंग में {{busy}}% व्यस्त। {{spread}}',
        'core-cpu-time.time_verdict_busy':
          '{{busy}}% व्यस्त है, और अधिकांश {{state}} ({{percent}}%) है{{next}}। {{spread}}',
        'core-cpu-time.time_verdict_second': ', फिर {{state}} ({{percent}}%)',
        'core-cpu-time.time_verdict_state.user': 'यूज़रस्पेस का सामान्य काम',
        'core-cpu-time.time_verdict_state.nice': 'कम प्राथमिकता का काम',
        'core-cpu-time.time_verdict_state.system': 'प्रोसेस के लिए कर्नेल काम',
        'core-cpu-time.time_verdict_state.iowait': 'स्टोरेज की प्रतीक्षा',
        'core-cpu-time.time_verdict_state.steal': 'हाइपरवाइज़र द्वारा लिया समय',
        'core-cpu-time.time_verdict_state.softirq': 'स्थगित इंटरप्ट काम',
        'core-cpu-time.time_verdict_state.irq': 'हार्डवेयर इंटरप्ट',
      }),
      ar: Object.freeze({
        'core-cpu-time.title': 'إلى أين يذهب الوقت',
        'core-cpu-time.help':
          'لا يكون المعالج «مشغولًا» فحسب. كل jiffy يقضيه يُحمّل على واحدة من ثماني حالات، وهي التي تقرر ما يلي — العمل في مساحة المستخدم هو ما تكسب به الآلة قوتها، والوقت في النواة عبء كثيف باستدعاءات النظام، وiowait مشكلة قرص ترتدي زي المعالج، وsteal خاص بمدير الأجهزة الافتراضية لا بك مطلقًا. نسبة الانشغال الوحيدة في بقية لوحة المعلومات هي مجموع كل هذه الحالات، ولهذا يُطرح السؤال عادة مرتين.',
        'core-cpu-time.meaning_title': 'ماذا يعني كل منها',
        'core-cpu-time.average_over_range':
          'متوسط {{percent}}% خلال هذا النطاق',
        'core-cpu-time.state': 'الحالة',
        'core-cpu-time.in_proc_stat': 'في /proc/stat',
        'core-cpu-time.now': 'الآن',
        'core-cpu-time.what_it_means': 'ما يعنيه',
        'core-cpu-time.cores_unknown': 'عدد النوى غير معروف',
        'core-cpu-time.state_label.steal': 'استولى عليه المضيف',
        'core-cpu-time.state_label.iowait': 'ينتظر التخزين',
        'core-cpu-time.state_label.softirq': 'عمل مقاطعة مؤجل',
        'core-cpu-time.state_label.irq': 'مقاطعات العتاد',
        'core-cpu-time.state_label.system': 'النواة، نيابة عن عملية',
        'core-cpu-time.state_label.user': 'مساحة المستخدم',
        'core-cpu-time.state_label.nice': 'مساحة المستخدم، أولوية منخفضة',
        'core-cpu-time.state_label.idle': 'خامل',
        'core-cpu-time.state_meaning.steal':
          'وقت كانت فيه هذه الآلة مستعدة للتشغيل لكن المشرف الافتراضي منح النواة الفعلية لشخص آخر. هذا ليس عملاً يُنجز هنا ولا شيئًا يمكن لتغيير على هذه الآلة تقليله. في الاستضافة المشتركة أو الافتراضية، يفسر هذا الرقم «الجهاز بطيء ولا يعمل شيء».',
        'core-cpu-time.state_meaning.iowait':
          'لم يكن لدى النواة ما تشغله وكان شيء ما محجوبًا على قرص. هذا وقت خمول لا استهلاك للمعالج — فالمعالج هنا ضحية لا المشكلة. ارتفاع iowait يعني فحص الأقراص؛ إضافة معالج لن تغير شيئًا.',
        'core-cpu-time.state_meaning.softirq':
          'النصف الثاني من معالجة المقاطعة، ويتم خارج المقاطعة نفسها. على الخادم يكون غالبًا مكدس الشبكة. امتلاء نواة كاملة به نتيجة تستحق الانتباه: فهو يعني عادة حجم الحزم لا عملاً تؤديه عملية ما.',
        'core-cpu-time.state_meaning.irq':
          'خدمة الأجهزة. يكون عادة جزءًا ضئيلاً من نسبة مئوية؛ وأي قيمة مرئية على هذا الرسم تشير إلى جهاز أو برنامج تشغيل.',
        'core-cpu-time.state_meaning.system':
          'وقت في النواة بالنيابة عن عملية ما: استدعاءات نظام، وأخطاء صفحات، وربط ذاكرة. زيادته عن وقت مساحة المستخدم تعني أن العمل كثيف باستدعاءات النظام — قراءات وكتابات صغيرة كثيرة وإنشاء عمليات كثير — لا حسابات.',
        'core-cpu-time.state_meaning.user':
          'عمل عادي بأولوية عادية: ما طلبت فعلاً من الآلة القيام به. آلة مشغولة في مساحة المستخدم فقط تعمل كما ينبغي، وقد يكون ذلك طبيعيًا تمامًا.',
        'core-cpu-time.state_meaning.nice':
          'العمل نفسه بأولوية مخفضة — نسخ احتياطية أو مهام مجمعة أو أي شيء يعمل تحت nice. يفسح المجال لكل ما عداه، لذلك فالآلة المشغولة هنا وحده لا تجعل أحدًا ينتظر.',
        'core-cpu-time.state_meaning.idle':
          'لا شيء للتشغيل ولا شيء ينتظر قرصًا أيضًا.',
        'core-cpu-time.cores_count': '{{count}} نواة',
        'core-cpu-time.time_verdict_spread':
          'النسب تخص {{cores}} كلها معاً، لذا تعني 100٪ انشغال الآلة كاملة.',
        'core-cpu-time.time_verdict_idle':
          'الآلة خاملة — مشغولة بنسبة {{busy}}٪ في آخر قراءة. {{spread}}',
        'core-cpu-time.time_verdict_busy':
          'الآلة مشغولة بنسبة {{busy}}٪، ومعظم ذلك {{state}} ({{percent}}٪){{next}}. {{spread}}',
        'core-cpu-time.time_verdict_second': '، ثم {{state}} ({{percent}}٪)',
        'core-cpu-time.time_verdict_state.user': 'عمل عادي في مساحة المستخدم',
        'core-cpu-time.time_verdict_state.nice': 'عمل منخفض الأولوية',
        'core-cpu-time.time_verdict_state.system': 'عمل النواة للعمليات',
        'core-cpu-time.time_verdict_state.iowait': 'انتظار التخزين',
        'core-cpu-time.time_verdict_state.steal': 'وقت أخذه المشرف الافتراضي',
        'core-cpu-time.time_verdict_state.softirq': 'عمل مقاطعة مؤجل',
        'core-cpu-time.time_verdict_state.irq': 'مقاطعات العتاد',
      }),
      zh: Object.freeze({
        'core-cpu-time.title': '时间都用在哪里',
        'core-cpu-time.help':
          '处理器从不会只是“忙碌”。它消耗的每个 jiffy 都会记入八种状态之一，而哪一种决定下一步该做什么：用户态工作是机器在做正事，内核时间是系统调用密集型负载，iowait 是披着 CPU 外衣的磁盘问题，steal 则是虚拟机监控程序的问题，完全不是您的问题。仪表板其他位置的单一忙碌百分比正是这些数值的总和，因此这个问题通常要问两遍。',
        'core-cpu-time.meaning_title': '它们各自代表什么',
        'core-cpu-time.average_over_range': '此范围内平均 {{percent}}%',
        'core-cpu-time.state': '状态',
        'core-cpu-time.in_proc_stat': '在 /proc/stat 中',
        'core-cpu-time.now': '现在',
        'core-cpu-time.what_it_means': '含义',
        'core-cpu-time.cores_unknown': '核心数未知',
        'core-cpu-time.state_label.steal': '被主机占用',
        'core-cpu-time.state_label.iowait': '正在等待存储',
        'core-cpu-time.state_label.softirq': '延迟中断处理',
        'core-cpu-time.state_label.irq': '硬件中断',
        'core-cpu-time.state_label.system': '内核（代表进程）',
        'core-cpu-time.state_label.user': '用户态',
        'core-cpu-time.state_label.nice': '低优先级用户态',
        'core-cpu-time.state_label.idle': '空闲',
        'core-cpu-time.state_meaning.steal':
          '这台机器本已准备好运行，但虚拟机监控程序将物理核心分配给了别人。这不是本机正在完成的工作，也不是改动本机就能减少的事情。在共享或虚拟化主机上，它解释了“机器很慢却没有程序在运行”。',
        'core-cpu-time.state_meaning.iowait':
          '核心没有可运行的任务，而某项工作被磁盘阻塞。这是空闲时间，不是 CPU 消耗——处理器在这里是受害者而非问题。iowait 很高时应查看磁盘；增加 CPU 不会改变任何事情。',
        'core-cpu-time.state_meaning.softirq':
          '中断处理的后半部分，在中断本身之外完成。在服务器上，它大多是网络协议栈。占满一个核心是值得关注的信号：它通常意味着数据包量，而不是某个进程正在做的工作。',
        'core-cpu-time.state_meaning.irq':
          '为设备提供服务。通常只占很小的百分比；此图表上任何可见的值都指向某个设备或驱动程序。',
        'core-cpu-time.state_meaning.system':
          '代表某个进程在内核中花费的时间：系统调用、缺页、映射内存。它多于用户态时间，说明工作以系统调用为主——大量小型读写和进程创建——而不是计算。',
        'core-cpu-time.state_meaning.user':
          '正常优先级下的普通工作：您实际要求机器完成的事情。一台只在用户态繁忙的机器正在工作，这可能完全正常。',
        'core-cpu-time.state_meaning.nice':
          '同样的工作，但以较低优先级运行——备份、批处理作业或任何以 nice 运行的内容。它会让出资源给其他工作，因此只在这里繁忙的机器不会让任何人等待。',
        'core-cpu-time.state_meaning.idle':
          '没有要运行的任务，也没有任务在等待磁盘。',
        'core-cpu-time.cores_count': '{{count}} 个核心',
        'core-cpu-time.time_verdict_spread':
          '百分比是所有 {{cores}} 合计的结果，因此 100% 表示整台机器都被占用。',
        'core-cpu-time.time_verdict_idle':
          '机器处于空闲状态——上一次读数中忙碌 {{busy}}%。{{spread}}',
        'core-cpu-time.time_verdict_busy':
          '忙碌 {{busy}}%，其中大部分是{{state}}（{{percent}}%）{{next}}。{{spread}}',
        'core-cpu-time.time_verdict_second':
          '，其次是{{state}}（{{percent}}%）',
        'core-cpu-time.time_verdict_state.user': '用户空间中的普通工作',
        'core-cpu-time.time_verdict_state.nice': '低优先级工作',
        'core-cpu-time.time_verdict_state.system': '代表进程执行的内核工作',
        'core-cpu-time.time_verdict_state.iowait': '等待存储',
        'core-cpu-time.time_verdict_state.steal': '虚拟机监控程序占用的时间',
        'core-cpu-time.time_verdict_state.softirq': '延迟的中断工作',
        'core-cpu-time.time_verdict_state.irq': '硬件中断',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-time.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-time.${key}`],
    );
  },
  render(input, helpers) {
    const { now, states, series, cores } = input;
    const {
      setCard,
      note,
      tableTarget,
      renderTable,
      bandOf,
      emptyMessage,
      i18n,
    } = helpers;
    const stateLabel = (meta) => {
      const key = String(meta?.short || '').trim();
      return key
        ? i18n.t(`core-cpu-time.state_label.${key}`, {}, meta.label)
        : meta.label;
    };
    const stateMeaning = (meta) => {
      const key = String(meta?.short || '').trim();
      return key
        ? i18n.t(`core-cpu-time.state_meaning.${key}`, {}, meta.what)
        : meta.what;
    };
    const chartLabels = Object.fromEntries(
      Object.entries(states).map(([key, meta]) => [key, stateLabel(meta)]),
    );
    const bands = {
      steal: [5, 20],
      iowait: [15, 35],
      softirq: [10, 25],
      irq: [5, 15],
    };
    if (!now) {
      if (note) note.textContent = emptyMessage;
    } else {
      for (const [key, meta] of Object.entries(states)) {
        const value = Number(now[key]);
        const values = series
          .map((row) => Number(row[key]))
          .filter(Number.isFinite);
        const average = values.length
          ? values.reduce((sum, one) => sum + one, 0) / values.length
          : null;
        const band = bands[key]
          ? bandOf(value, bands[key][0], bands[key][1])
          : null;
        setCard(
          stateLabel(meta),
          Number.isFinite(value) ? `${value.toFixed(1)}%` : '—',
          average == null
            ? ''
            : i18n.t(
                'core-cpu-time.average_over_range',
                { percent: average.toFixed(1) },
                '{{percent}}% on average over this range',
              ),
          band === 'crit' ? 'crit' : band === 'warn' ? 'warn' : '',
        );
      }
    }
    if (now && note) note.textContent = cpuTimeVerdict(now, cores, i18n);
    if (!tableTarget) return { chartLabels };
    renderTable(
      tableTarget.id,
      Object.entries(states).map(([key, meta]) => ({
        key,
        label: stateLabel(meta),
        now: now ? Number(now[key]) : null,
        what: stateMeaning(meta),
      })),
      [
        {
          label: i18n.t('core-cpu-time.state', {}, 'State'),
          render: (row) => row.label,
        },
        {
          label: i18n.t('core-cpu-time.in_proc_stat', {}, 'In /proc/stat'),
          className: 'cmd',
          render: (row) => row.key,
        },
        {
          label: i18n.t('core-cpu-time.now', {}, 'Now'),
          className: 'size',
          render: (row) =>
            row.now == null || !Number.isFinite(row.now)
              ? '—'
              : `${row.now.toFixed(1)}%`,
        },
        {
          label: i18n.t('core-cpu-time.what_it_means', {}, 'What it means'),
          render: (row) => row.what,
        },
      ],
    );
    return { chartLabels };
  },
};

function cpuTimeVerdict(now, cores, i18n) {
  const busy = Number(now.busy);
  const spread = i18n.t(
    'core-cpu-time.time_verdict_spread',
    {
      cores: cores
        ? i18n.t(
            'core-cpu-time.cores_count',
            { count: cores },
            '{{count}} cores',
          )
        : i18n.t('core-cpu-time.cores_unknown', {}, 'cores unknown'),
    },
    'Percentages are of all {{cores}} together, so 100% would be the whole machine occupied.',
  );
  if (busy < 5)
    return i18n.t(
      'core-cpu-time.time_verdict_idle',
      { busy: busy.toFixed(1), spread },
      'The machine is idle — {{busy}}% busy over the last reading. {{spread}}',
    );
  const parts = [
    ['user', 'ordinary work in userland'],
    ['nice', 'low-priority work'],
    ['system', 'kernel work on behalf of processes'],
    ['iowait', 'waiting for storage'],
    ['steal', 'time the hypervisor took away'],
    ['softirq', 'deferred interrupt work'],
    ['irq', 'hardware interrupts'],
  ].map(([key, fallback]) => [
    key,
    i18n.t(`core-cpu-time.time_verdict_state.${key}`, {}, fallback),
  ]);
  const ranked = parts
    .map(([key, words]) => [Number(now[key]) || 0, words])
    .sort((a, b) => b[0] - a[0]);
  const [top, topWords] = ranked[0];
  const [second, secondWords] = ranked[1];
  const next =
    second >= top / 3
      ? i18n.t(
          'core-cpu-time.time_verdict_second',
          { state: secondWords, percent: second.toFixed(1) },
          ', then {{state}} ({{percent}}%)',
        )
      : '';
  return i18n.t(
    'core-cpu-time.time_verdict_busy',
    {
      busy: busy.toFixed(0),
      state: topWords,
      percent: top.toFixed(1),
      next,
      spread,
    },
    '{{busy}}% busy, and most of it is {{state}} ({{percent}}%){{next}}. {{spread}}',
  );
}
