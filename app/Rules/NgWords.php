<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NgWords implements ValidationRule
{
    // 100単語のNGワードリスト（英語）
    public $ngWordsEnglish = [
        'abomination', 'abuse', 'assault', 'bastard', 'bitch', 'bigotry', 'brutality', 'bully', 'cowardice', 'corruption',
        'damn', 'death threat', 'defamation', 'degradation', 'disgusting', 'dishonesty', 'disrespect', 'divisive', 'evil',
        'fascism', 'fraud', 'genocide', 'hate speech', 'harassment', 'hypocrisy', 'intolerance', 'injustice', 'insult',
        'jerk', 'kidnapping', 'kill', 'lie', 'malign', 'malicious', 'murder', 'nasty', 'neglect', 'obscene',
        'offensive', 'oppression', 'persecution', 'prejudice', 'racism', 'rape', 'repulsive', 'scam', 'scandalous', 'segregation',
        'sexism', 'shameful', 'slander', 'slavery', 'stupidity', 'terrorism', 'toxic', 'treason', 'unfair', 'vicious',
        'vile', 'violence', 'vulgarity', 'wicked', 'xenophobia', 'abortion', 'alcohol', 'drugs', 'gambling', 'prostitution',
        'smoking', 'suicide', 'pornography', 'nudity', 'obscenity', 'adultery', 'incest', 'pedophilia', 'bestiality', 'corruption',
        'fraud', 'embezzlement', 'bribery', 'extortion', 'counterfeit', 'plagiarism', 'piracy', 'forgery', 'smuggling',
        'terrorism', 'treason', 'sedition', 'espionage', 'riot', 'arson', 'kidnapping', 'assassination', 'hijacking', 'sabotage'
    ];

    // Google 翻訳による日本語翻訳
    public $ngWordsJapanese = [
        '忌まわしい', '虐待', '攻撃', '私生児', '雌犬', '偏見', '残虐行為', 'いじめっ子', '臆病', '腐敗',
        '畜生', '死亡予告', '中傷', '劣化', '不快', '不正直', '無礼', '分裂的', '悪',
        'ファシズム', '詐欺', '虐殺', '憎悪表現', '嫌がらせ', '偽善', '不寛容', '不正', '侮辱',
        '馬鹿', '誘拐', '殺す', '嘘', '悪意的', '悪意のある', '殺人', '不快な', '無視', 'わいせつ',
        '攻撃的', '抑圧', '迫害', '偏見', '人種差別', 'レイプ', '嫌悪', '詐欺', 'スキャンダラス', '隔離',
        '性差別', '恥ずかしい', '中傷', '奴隷制度', '愚かさ', 'テロリスト', '有毒', '反逆', '不公平', '悪意のある',
        '卑劣な', '暴力', '下品', '邪悪', '排外主義', '中絶', 'アルコール', '薬物', 'ギャンブル', '売春',
        '喫煙', '自殺', 'ポルノグラフィー', '裸', '猥褻', '姦通', '近親相姦', '小児性愛', '獣姦', '腐敗',
        '詐欺', '横領', '贈賄', '恐喝', '偽造', '盗作', '海賊行為', '偽造', '密輸',
        'テロ', '反逆', '反逆', 'スパイ行為', '暴動', '放火', '誘拐', '暗殺', 'ハイジャック', '妨害'
    ];

    // 合成されたNGワードリスト
    protected $ngWords;

    /**
     * NgWordsクラスの新しいインスタンスを作成
     *
     * @param  array  $ngWords NGワードの配列
     * @return void
     */
    public function __construct()
    {
        $this->ngWords = array_merge($this->ngWordsEnglish, $this->ngWordsJapanese);
    }

    /**
     * バリデーションを実行
     *
     * @param  string  $attribute バリデーション対象の属性名
     * @param  mixed  $value バリデーション対象の値
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($attribute, $this->ngWords)) {
            $fail("The :attribute field is not allowed. Allowed fields");
        }
    }

    /**
     * バリデーションを実行した結果の合否判定
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach ($this->ngWords as $word) {
            if (stripos($value, $word) !== false) {
                return false;
            }
        }
        return true;
    }
}
