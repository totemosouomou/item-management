const fs = require('fs');
const path = require('path');

// 引数からディレクトリ名とファイルパスを取得
const dirname = process.argv[2];
const filepath = path.join('/tmp', dirname, 'testfile.txt');

// ディレクトリが存在しない場合は作成
if (!fs.existsSync(path.join('/tmp', dirname))) {
    fs.mkdirSync(path.join('/tmp', dirname), { recursive: true });
}

// ファイルに書き込み
fs.writeFileSync(filepath, 'Hello, Heroku!', 'utf8');
console.log('File written to', filepath);
