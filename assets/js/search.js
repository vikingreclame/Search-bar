(function () {
  const config = window.workwearAjaxSearch || {};
  const ajaxUrl = config.ajaxUrl;
  const nonce = config.nonce;
  const minChars = Number(config.minChars || 2);

  const strings = Object.assign(
    {
      typeToSearch: 'Typ om te zoeken…',
      loading: 'Zoeken…',
      noResults: 'Geen resultaten gevonden.',
      viewAll: 'Bekijk alle resultaten',
    },
    config.strings || {}
  );

  const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(null, args), delay);
    };
  };

  const escapeHtml = (unsafe) =>
    unsafe
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

  const buildResultItem = (product) => {
    const image = product.image
      ? `<img class="wwas-search__thumb" src="${product.image}" alt="${escapeHtml(product.title)}" loading="lazy" />`
      : '<span class="wwas-search__thumb wwas-search__thumb--placeholder" aria-hidden="true"></span>';

    const skuHtml = product.sku ? `<small class="wwas-search__sku">SKU: ${escapeHtml(product.sku)}</small>` : '';

    return `
      <li class="wwas-search__item">
        <a class="wwas-search__link" href="${product.url}">
          ${image}
          <span class="wwas-search__meta">
            <span class="wwas-search__title">${escapeHtml(product.title)}</span>
            ${skuHtml}
            ${product.priceHtml ? `<span class="wwas-search__price">${product.priceHtml}</span>` : ''}
          </span>
        </a>
      </li>
    `;
  };

  const setStatus = (container, message) => {
    const status = container.querySelector('.wwas-search__status');
    if (status) {
      status.textContent = message;
    }
  };

  const setResults = (container, data, term) => {
    const resultsWrap = container.querySelector('.wwas-search__results');
    const list = container.querySelector('.wwas-search__list');
    const allLink = container.querySelector('.wwas-search__all');

    if (!resultsWrap || !list || !allLink) {
      return;
    }

    if (!data.results || !data.results.length) {
      list.innerHTML = '';
      setStatus(container, strings.noResults);
      allLink.hidden = true;
      resultsWrap.hidden = false;
      return;
    }

    list.innerHTML = data.results.map(buildResultItem).join('');
    setStatus(container, `"${term}"`);

    allLink.href = data.searchUrl;
    allLink.textContent = `${strings.viewAll} (${data.count})`;
    allLink.hidden = false;
    resultsWrap.hidden = false;
  };

  const doSearch = async (container, term) => {
    if (!ajaxUrl || !nonce) {
      return;
    }

    const list = container.querySelector('.wwas-search__list');
    const resultsWrap = container.querySelector('.wwas-search__results');
    const allLink = container.querySelector('.wwas-search__all');

    if (!list || !resultsWrap || !allLink) {
      return;
    }

    if (term.length < minChars) {
      list.innerHTML = '';
      setStatus(container, strings.typeToSearch);
      allLink.hidden = true;
      resultsWrap.hidden = false;
      return;
    }

    setStatus(container, strings.loading);
    resultsWrap.hidden = false;

    const params = new URLSearchParams();
    params.append('action', 'workwear_ajax_product_search');
    params.append('nonce', nonce);
    params.append('term', term);
    params.append('limit', container.dataset.maxResults || '8');
    params.append('showPrice', container.dataset.showPrice || 'yes');
    params.append('showImage', container.dataset.showImage || 'yes');

    try {
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: params.toString(),
      });

      const payload = await response.json();

      if (!payload.success) {
        throw new Error('Search request failed');
      }

      setResults(container, payload.data, term);
    } catch (error) {
      setStatus(container, strings.noResults);
      list.innerHTML = '';
      allLink.hidden = true;
    }
  };

  const initSearch = (container) => {
    const input = container.querySelector('.wwas-search__input');
    const resultsWrap = container.querySelector('.wwas-search__results');

    if (!input || !resultsWrap) {
      return;
    }

    const debouncedSearch = debounce((value) => doSearch(container, value), 250);

    input.addEventListener('input', (event) => {
      debouncedSearch(event.target.value.trim());
    });

    input.addEventListener('focus', () => {
      setStatus(container, strings.typeToSearch);
      resultsWrap.hidden = false;
    });

    document.addEventListener('click', (event) => {
      if (!container.contains(event.target)) {
        resultsWrap.hidden = true;
      }
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wwas-search').forEach(initSearch);
  });
})();
