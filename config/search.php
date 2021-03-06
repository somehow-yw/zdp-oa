<?php

return [
    'debug' => env('APP_DEBUG', true),
    // the elastic search servers
    'servers' => [
        [
            'host' => env('SEARCH_HOST', 'localhost'),
            'port' => env('SEARCH_PORT', '9200'),
        ],
    ],

    'index' => [
        'alias'     => env('SEARCH_INDEX_ALIAS', 'zdp'),
    ],

    'zdp_synonym'   => [
        // chiken
        '鸡脖子,鸡脖',
        '鸡脑壳,鸡头',
        '鸡心子,鸡心',
        '鸡冠子,鸡冠',
        '鸡大胸,鸡脯肉,鸡脯,鸡胸肉,鸡胸',
        '鸡翅中,鸡翅,鸡中翅',
        '鸡排翅,鸡全翅',
        '鸡翅尖,鸡尖',
        '鸡小腿,鸡腿,琵琶腿',
        '鸡边,鸡边腿',
        '鸡脚,凤爪,鸡爪',
        '去骨鸡脚,去骨凤爪,无骨鸡爪,无骨凤爪,去骨鸡爪',
        '膝软骨,掌中宝',
        '去骨鸡腿肉,去骨腿肉',
        '鸡数子,鸡素子,鸡肚,鸽肚,凤肚,鸡嗉子',
        '鸡肫,鸡菌肝,鸡郡肝,鸡郡,鸡屯,鸡肫,鸡菌干,鸡君肝,鸡君,鸡菌胗,鸡珍',
        '鸡板油,老鸡油,鸡大脂,鸡油',
        '鸡骨架,鸡架',
        '黑脚鸡,麻鸡,青脚鸡',
        '冻鸡,整鸡,全鸡,老母鸡',
        '白凤乌鸡,乌骨鸡,乌鸡',
        '鸡屁股,鸡尾巴,鸡尾',

        // duck
        '鸭脑壳,鸭头',
        '鸭舌头,鸭设,鸭舍,鸭舌',
        '鸭心子,鸭心',
        '鸭肝子,鸭肝',
        '生扣鸭肠,鸭肠',
        '鸭肫,鸭菌肝,鸭郡肝,鸭郡,鸭屯,鸭肫,鸭菌干,鸭君肝,鸭君,鸭菌胗,鸭珍,鸭胗',
        '鸭食管,食管肠,鸭君把,鸭君爸,鸭菌把,鸭巴子,鸭把子,郡把子,菌把子,君把子,鸭食带',
        '鸭架,鸭骨架,鸭锁骨',
        '鸭脚,鸭爪,鸭掌',
        '鸭翅,鸭翅膀,鸭二节翅,鸭二节',
        '鸭大胸,鸭脯,鸭胸肉,鸭胸',
        '半边鸭,半鸭,边鸭,半片鸭',
        '瘦肉型冻鸭,整鸭,鸭子,冻鸭,全鸭,白条鸭',
        '老鸭子,老鸭',
        '去骨鸭掌,去骨鸭爪,无骨鸭脚,无骨鸭掌,无骨鸭爪,脱骨鸭爪,脱骨鸭掌,脱骨鸭脚,去骨鸭脚',
        '鸭下唇,鸭唇',
        '鹅天堂,鸭天堂',

        // goose
        '鹅脑壳,鹅头',
        '鹅翅膀,鹅二节翅,鹅全翅,鹅翅膀,鹅翅',
        '东北鹅肠,鹅肠',
        '鹅肫,鹅菌肝,鹅郡肝,鹅郡,鹅屯,鹅肫,鹅菌干,鹅君肝,鹅君,鹅菌胗,鹅珍',
        '鹅食管,鹅管肠,鹅君把,鹅君爸,鹅菌把,鹅巴子,鹅把子,鹅胗带,鹅把子,鹅把子,鹅把子,鹅食带',
        '白条鹅,冻鹅,鹅儿,鹅肝',

        // rabbit
        '兔脑壳,兔头',
        '兔前腿,兔后退,兔后腿,兔腿',
        '兔儿肚,兔肚',
        '兔儿腰,兔腰',
        '兔肉,兔子,保鲜兔,冷鲜兔,鲜兔,中级兔,干兔,白条兔',

        // pig
        '猪脑壳,猪头',
        '脑壳皮,猪头皮',
        '猪耳叶,猪耳页,猪耳朵',
        '猪口条,猪利子,猪栗子,猪舌头',
        '猪牙根,猪牙卡,猪天梯,猪牙板,猪天堂',
        '猪嘴,猪脸,猪脸拱,猪鼻子,猪拱嘴',
        '猪脚,猪手,猪蹄',
        '猪肚子,猪大肚',
        '猪尿包,猪小肚,小肚',
        '猪大肠,冻肥肠,熟大肠,熟肥肠,猪肥肠',
        '猪心管,猪喉管',
        '猪前排,猪中排,猪大排,猪精排,猪肋排,猪排骨',
        '猪油,猪板油',

        // ox
        '牛前腿,牛前,牛后腿,牛后,牛腿',
        '牛林肉,牛霖肉,霖肉',
        '牛腱子,牛建子,牛件子,牛健子,腱子肉',
        '碎牛肉,牛碎肉',
        '牛骨髓,牛脊髓',

        // brand
        '六合,六和',
    ],
];
