import puppeteer from 'puppeteer';

// コマンドライン引数からURLと保存先のパスを取得する
const url = process.argv[2];
const path = process.argv[3];
const storagePath = process.argv[4];

console.log(url, path, storagePath);

(async () => {
    try {
        const browser = await puppeteer.launch({
            executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/app/.cache/puppeteer/chrome/chrome-linux/chrome',
            userDataDir: storagePath,
        });
        const page = await browser.newPage();

        // 指定のURLに移動
        await page.goto(url, { waitUntil: 'networkidle2' });

        // ページ全体のスクリーンショットを撮る
        await page.screenshot({ path: path, fullPage: true });

        await browser.close();
        console.log('Screenshot saved successfully.');
    } catch (error) {
        console.error('Failed to generate screenshot:', error);
        process.exit(1);
    }
})();
