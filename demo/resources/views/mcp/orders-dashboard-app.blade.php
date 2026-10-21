<x-mcp::app :title="$title">
    <x-slot:head>
        <style>
            :root { color-scheme: light dark; }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                padding: 24px;
                font: 14px/1.5 var(--font-sans, system-ui, sans-serif);
                color: var(--color-text-primary, light-dark(#202124, #f3f4f6));
                background: var(--color-background-primary, light-dark(#fff, #171717));
            }
            header { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
            h1 { margin: 0; font-size: 22px; font-weight: 650; }
            p { margin: 4px 0 0; }
            .muted { color: var(--color-text-secondary, light-dark(#555, #b5b5b5)); }
            button { font: inherit; font-weight: 600; padding: 9px 14px; border: 1px solid var(--color-border-primary, light-dark(#ccc, #555)); border-radius: 6px; background: var(--color-background-primary, light-dark(#fff, #171717)); color: inherit; cursor: pointer; }
            button:hover { background: var(--color-background-secondary, light-dark(#f3f4f6, #292929)); }
            button:focus-visible, .table-wrap:focus-visible { outline: 2px solid currentColor; outline-offset: 3px; }
            button:disabled { opacity: .6; cursor: wait; }
            .table-wrap { overflow-x: auto; }
            table { width: 100%; border-collapse: collapse; white-space: nowrap; }
            th, td { padding: 13px 12px; text-align: left; border-bottom: 1px solid var(--color-border-primary, light-dark(#e5e7eb, #383838)); }
            th { font-size: 12px; font-weight: 600; color: var(--color-text-secondary, light-dark(#555, #b5b5b5)); }
            th:first-child, td:first-child { padding-left: 0; }
            th:last-child, td:last-child { padding-right: 0; text-align: right; font-variant-numeric: tabular-nums; }
            .status { display: inline-block; padding: 3px 9px; border-radius: 4px; background: var(--color-background-secondary, light-dark(#f0f1f3, #303030)); }
            #feedback { padding: 20px 0; }
            #feedback[data-error="true"] { color: light-dark(#b42318, #ffb4ab); }
            [hidden] { display: none !important; }
            @media (max-width: 480px) { body { padding: 16px; } th, td { padding: 12px 8px; } }
        </style>
        <script type="module">
            const refresh = document.getElementById('refresh-orders');
            const feedback = document.getElementById('feedback');
            const table = document.getElementById('orders-table');
            const rows = document.getElementById('orders-body');
            const count = document.getElementById('orders-count');
            const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
            const date = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' });

            function showError(error) {
                feedback.textContent = error.message || 'Não foi possível carregar os pedidos. Tente atualizar a lista.';
                feedback.dataset.error = 'true';
                feedback.hidden = false;
            }

            function render(result) {
                if (result.isError) {
                    throw new Error(result.content?.find(item => item.type === 'text')?.text || 'Não foi possível carregar os pedidos.');
                }

                let data = result.structuredContent;
                if (!data) {
                    const text = result.content?.find(item => item.type === 'text')?.text;
                    if (text) data = JSON.parse(text);
                }
                if (!Array.isArray(data?.orders)) return false;

                rows.replaceChildren();
                for (const order of data.orders) {
                    const row = document.createElement('tr');
                    const created = order.created_at ? new Date(order.created_at) : null;
                    const values = [
                        `#${order.id}`,
                        created && !Number.isNaN(created.getTime()) ? date.format(created) : '—',
                        order.status?.name || 'Sem status',
                        order.total_amount != null && Number.isFinite(Number(order.total_amount)) ? currency.format(Number(order.total_amount)) : '—',
                    ];
                    values.forEach((value, index) => {
                        const cell = document.createElement('td');
                        const label = index === 2 ? document.createElement('span') : cell;
                        if (index === 2) { label.className = 'status'; cell.append(label); }
                        label.textContent = value;
                        row.append(cell);
                    });
                    rows.append(row);
                }
                count.textContent = `${data.orders.length} ${data.orders.length === 1 ? 'pedido' : 'pedidos'}`;
                table.hidden = data.orders.length === 0;
                feedback.dataset.error = 'false';
                feedback.textContent = 'Nenhum pedido encontrado. Atualize a lista para consultar novamente.';
                feedback.hidden = data.orders.length > 0;
                return true;
            }

            createMcpApp(async (app) => {
                async function loadOrders() {
                    refresh.disabled = true;
                    refresh.textContent = 'Carregando…';
                    document.getElementById('orders').setAttribute('aria-busy', 'true');
                    feedback.dataset.error = 'false';
                    feedback.textContent = 'Carregando pedidos…';
                    feedback.hidden = false;
                    try {
                        if (!render(await app.callServerTool('list-orders', {}))) {
                            throw new Error('O servidor retornou uma resposta sem a lista de pedidos.');
                        }
                    } catch (error) {
                        showError(error);
                    } finally {
                        refresh.disabled = false;
                        refresh.textContent = 'Atualizar pedidos';
                        document.getElementById('orders').setAttribute('aria-busy', 'false');
                    }
                }

                app.onToolResult(result => {
                    try { render(result); } catch (error) { showError(error); }
                });
                refresh.disabled = false;
                refresh.addEventListener('click', loadOrders);
                await loadOrders();
            }).catch(() => showError(new Error('Não foi possível conectar ao app. Reabra o painel e tente novamente.')));
        </script>
    </x-slot:head>

    <main id="orders" aria-labelledby="orders-title">
        <header>
            <div>
                <h1 id="orders-title">Meus pedidos</h1>
                <p id="orders-count" class="muted" aria-live="polite">Consulte seus pedidos mais recentes.</p>
            </div>
            <button id="refresh-orders" type="button" disabled>Atualizar pedidos</button>
        </header>
        <p id="feedback" role="status" aria-live="polite">Conectando ao app…</p>
        <div class="table-wrap" role="region" aria-label="Lista de pedidos" tabindex="0">
            <table id="orders-table" aria-label="Pedidos" hidden>
                <thead><tr><th scope="col">Pedido</th><th scope="col">Data</th><th scope="col">Status</th><th scope="col">Total</th></tr></thead>
                <tbody id="orders-body"></tbody>
            </table>
        </div>
    </main>
</x-mcp::app>
