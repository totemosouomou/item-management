<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $comments配列を定義
        $comments = [
            ['comment' => '素晴らしい洞察力ですね！'],
            ['comment' => 'とても魅力的なアイデアです！'],
            ['comment' => 'なるほど、その視点は新鮮ですね。'],
            ['comment' => 'ユニークな考え方ですね、感心しました。'],
            ['comment' => '的確なコメントです、ありがとうございます！'],
            ['comment' => '参考になる情報です、感謝します。'],
            ['comment' => '的を射た指摘です、考えさせられます。'],
            ['comment' => 'とても興味深い見解ですね！'],
            ['comment' => 'わかりやすく説明されています、感謝します。'],
            ['comment' => 'スマートなコメントです、勉強になります。'],
            ['comment' => '素晴らしいアイデアをありがとうございます！'],
            ['comment' => '洞察に満ちたコメントですね、共感します。'],
            ['comment' => 'その視点は新鮮で面白いですね。'],
            ['comment' => '分かりやすく説明されていて助かります。'],
            ['comment' => '的確なコメント、感謝します！'],
            ['comment' => '非常に興味深いアイデアですね。'],
            ['comment' => '的を射たアドバイスです、感謝します。'],
            ['comment' => '独創的な発想です、素晴らしいですね。'],
            ['comment' => '興味深い視点を提供していただきありがとうございます。'],
            ['comment' => '理解しやすい説明、感謝します。'],
            ['comment' => '参考になる情報をありがとうございます！'],
        ];

        // 各アイテムごとにループ
        for ($i = 1; $i <= 50; $i++) {
            // 各ユーザーごとにループ
            for ($j = 1; $j <= 32; $j++) {
                // ランダムにコメントを選択
                $randomComment = $comments[array_rand($comments)];

                // ユーザーを取得
                $user = User::find($j);

                // Postを作成
                DB::table('posts')->insert([
                    'user_id' => $user->id,
                    'item_id' => $i,
                    'post' => $randomComment['comment'] . " by " . $user->name,
                ]);
            }
        }

        // 生成したレコードをランダムに削除する
        $deleteCount = 1250;
        $posts = DB::table('posts')->inRandomOrder()->take($deleteCount)->pluck('id');

        DB::table('posts')->whereIn('id', $posts)->delete();

    }
}
