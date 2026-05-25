@if ($paginator->hasPages())
    <nav class="nexo-pagination" role="navigation" aria-label="Paginação">
        @if ($paginator->onFirstPage())
            <span class="nexo-page-step is-disabled"><i class="bi bi-chevron-left"></i></span>
        @else
            <a class="nexo-page-step" href="{{ $paginator->previousPageUrl() }}" rel="prev" data-nexo-page-link><i class="bi bi-chevron-left"></i></a>
        @endif

        <div class="nexo-page-numbers">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="nexo-page-dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="nexo-page-number is-active">{{ $page }}</span>
                        @else
                            <a class="nexo-page-number" href="{{ $url }}" data-nexo-page-link>{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="nexo-page-step" href="{{ $paginator->nextPageUrl() }}" rel="next" data-nexo-page-link><i class="bi bi-chevron-right"></i></a>
        @else
            <span class="nexo-page-step is-disabled"><i class="bi bi-chevron-right"></i></span>
        @endif
    </nav>

    @once
        <script>
            (function () {
                if (window.__nexoAjaxPaginationInitialized) {
                    return;
                }

                window.__nexoAjaxPaginationInitialized = true;

                const executeInlineScripts = function (container) {
                    container.querySelectorAll('script').forEach(function (oldScript) {
                        const newScript = document.createElement('script');

                        Array.from(oldScript.attributes).forEach(function (attribute) {
                            newScript.setAttribute(attribute.name, attribute.value);
                        });

                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                };

                const replaceMainContent = async function (url, shouldUpdateHistory) {
                    const currentMain = document.querySelector('main.nexo-main');

                    if (!currentMain) {
                        window.location.href = url;
                        return;
                    }

                    const currentScrollY = window.scrollY;

                    currentMain.style.transition = 'opacity 180ms ease, transform 180ms ease, filter 180ms ease';
                    currentMain.style.opacity = '0.62';
                    currentMain.style.transform = 'translateY(4px)';
                    currentMain.style.filter = 'blur(0.4px)';
                    currentMain.style.pointerEvents = 'none';

                    try {
                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html, application/xhtml+xml'
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            throw new Error('Falha ao carregar a paginação.');
                        }

                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const nextMain = doc.querySelector('main.nexo-main');

                        if (!nextMain) {
                            window.location.href = url;
                            return;
                        }

                        currentMain.innerHTML = nextMain.innerHTML;
                        currentMain.className = nextMain.className;

                        Array.from(nextMain.attributes).forEach(function (attribute) {
                            currentMain.setAttribute(attribute.name, attribute.value);
                        });

                        Array.from(currentMain.attributes).forEach(function (attribute) {
                            if (!nextMain.hasAttribute(attribute.name)) {
                                currentMain.removeAttribute(attribute.name);
                            }
                        });

                        executeInlineScripts(currentMain);

                        if (shouldUpdateHistory) {
                            history.pushState({ nexoAjaxPaginationUrl: url }, '', url);
                        }

                        window.scrollTo({
                            top: currentScrollY,
                            left: 0,
                            behavior: 'instant'
                        });

                        requestAnimationFrame(function () {
                            currentMain.style.opacity = '1';
                            currentMain.style.transform = 'translateY(0)';
                            currentMain.style.filter = 'blur(0)';
                            currentMain.style.pointerEvents = '';
                        });
                    } catch (error) {
                        window.location.href = url;
                    }
                };

                document.addEventListener('click', function (event) {
                    const link = event.target.closest('[data-nexo-page-link]');

                    if (!link) {
                        return;
                    }

                    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') {
                        return;
                    }

                    event.preventDefault();

                    replaceMainContent(link.href, true);
                });

                window.addEventListener('popstate', function () {
                    replaceMainContent(window.location.href, false);
                });
            })();
        </script>
    @endonce
@endif
