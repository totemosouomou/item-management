import puppeteer from 'puppeteer';

const url = process.argv[2];
const path = process.argv[3];

(async () => {
    try {
        const browser = await puppeteer.launch({
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        const page = await browser.newPage();

        // 指定のURLに移動
        await page.goto(url, { waitUntil: 'networkidle2' });

        // ページ全体のスクリーンショットを撮る
        await page.screenshot({ path, fullPage: true });

        await browser.close();
        process.exit(0);
    } catch (error) {
        console.error('Failed to generate screenshot:', error);
        process.exit(1);
    }
})();
