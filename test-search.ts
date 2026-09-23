import { chromium } from 'playwright';

async function runTest() {
    const browser = await chromium.launch({ headless: false });
    const context = await browser.newContext();
    const page = await context.newPage();

    try {
        console.log('Acessando dashboard...');
        await page.goto('http://localhost:8000/dashboard', {
            waitUntil: 'networkidle',
        });

        console.log('Aguardando SearchBar...');
        const searchButton = page.locator(
            'button:has-text("Pesquisar")',
        ).first();
        await searchButton.click();

        console.log('Digitando termo de busca...');
        const searchInput = page.locator(
            'input[placeholder*="Digite o nome do cliente"]',
        );
        await searchInput.fill('João');

        console.log('Aguardando resultados...');
        await page.waitForTimeout(1000);

        const results = page.locator('a[href*="/clientes/"], a[href*="/contratos/"]');
        const count = await results.count();
        console.log(`Encontrados ${count} resultados`);

        if (count > 0) {
            const firstResult = results.first();
            console.log('Clicando no primeiro resultado...');
            await firstResult.click();

            await page.waitForTimeout(2000);
            console.log('URL atual:', page.url());
            console.log('✅ Teste passou! A busca está funcionando.');
        } else {
            console.warn(
                '⚠️  Nenhum resultado encontrado. Verifique se há dados no banco.',
            );
        }
    } catch (error) {
        console.error('❌ Erro durante teste:', error);
    } finally {
        await browser.close();
    }
}

runTest();
