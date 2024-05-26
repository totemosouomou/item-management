const fs = require('fs');
const path = require('path');

const directory = process.argv[2];
const filename = process.argv[3];
const content = process.argv[4];

const filePath = path.join(directory, filename);

// フォルダが存在しない場合は作成
if (!fs.existsSync(directory)) {
    fs.mkdirSync(directory, { recursive: true });
}

// ファイルに内容を書き込む
fs.writeFileSync(filePath, content, 'utf8');

console.log(`File written to ${filePath}`);
