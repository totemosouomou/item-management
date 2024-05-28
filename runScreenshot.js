// runScreenshot.js

import { spawn } from 'child_process';

const url = 'https://qiita.com/ridanieru820/items/922be595c48c7de4f83d';
const path = 'C:/laravel/item-management/storage/tmp/bookmarks/example.png';
const storagePath = '/tmp/bookmarks';

if (!url || !path || !storagePath) {
    console.error('URL、保存先のパス、ストレージパスを指定してください。');
    process.exit(1);
}

const nodeScript = 'screenshot.js'; // screenshot.jsのパスに応じて変更してください

const process = spawn('node', [nodeScript, url, path, storagePath]);

process.stdout.on('data', (data) => {
    console.log(`stdout: ${data}`);
});

process.stderr.on('data', (data) => {
    console.error(`stderr: ${data}`);
});

process.on('close', (code) => {
    console.log(`child process exited with code ${code}`);
});
