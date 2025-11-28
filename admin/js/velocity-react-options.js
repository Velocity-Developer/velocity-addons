(() => {
  const appData = window.VelocityAddonsOptions || {};
  const rootId = 'velocity-addons-react-root';
  if (
    !window.wp ||
    !wp.element ||
    !wp.apiFetch ||
    !appData.routes ||
    !appData.routes.options
  ) {
    return;
  }

  const { createElement: h, Fragment, render, useEffect, useState, useRef } = wp.element;
  const apiFetch = wp.apiFetch;
  const fields = Array.isArray(appData.fields) ? appData.fields : [];
  const tabs = Array.isArray(appData.tabs) ? appData.tabs : [];
  const layout = (() => {
    if (appData.layout) return appData.layout;
    const tabId = (tabs[0] && tabs[0].id) || '';
    if (tabs.length === 1 && tabId === 'snippet') {
      return 'list';
    }
    return 'grid';
  })();
  const strings = Object.assign(
    {
      title: 'Pengaturan Velocity (React)',
      subtitle: 'Kelola opsi inti Velocity Addons melalui REST API.',
      save: 'Simpan',
      saving: 'Menyimpan...',
      updated: 'Pengaturan berhasil disimpan.',
      loadError: 'Gagal memuat pengaturan.',
      saveError: 'Gagal menyimpan pengaturan.',
    },
    appData.strings || {}
  );

  const defaultForField = (field) => {
    if (field.type === 'boolean' || field.type === 'checkbox') {
      return !!field.default;
    }
    if (field.type === 'number' || field.type === 'media') {
      return typeof field.default === 'number' ? field.default : 0;
    }
    if (field.type === 'array') {
      return Array.isArray(field.default) ? field.default : [];
    }
    if (field.type === 'select') {
      if (field.default !== undefined) {
        return field.default;
      }
      const choices = Array.isArray(field.choices) ? field.choices : [];
      return choices.length ? choices[0] : '';
    }
    return field.default !== undefined ? field.default : '';
  };

  const baseValues = fields.reduce((acc, field) => {
    const optionKey = field.id || field.key;
    const subKey = field.sub || null;
    const defVal = defaultForField(field);

    if (subKey) {
      const current = typeof acc[optionKey] === 'object' && acc[optionKey] !== null ? acc[optionKey] : {};
      acc[optionKey] = Object.assign({}, current, { [subKey]: defVal });
    } else {
      acc[optionKey] = defVal;
    }
    return acc;
  }, {});

  if (appData.nonce) {
    apiFetch.use(apiFetch.createNonceMiddleware(appData.nonce));
  }

  if (appData.root) {
    apiFetch.use(apiFetch.createRootURLMiddleware(appData.root));
  }

  const fieldMap = fields.reduce((map, field) => {
    const key = field.key || field.id || '';
    if (key) {
      map[key] = field;
    }
    return map;
  }, {});

  const OptionField = ({ field, value, onChange }) => {
    const description = field.description ? h('p', { className: 'vd-desc' }, field.description) : null;
    const optionKey = field.id || field.key;
    const subKey = field.sub || null;
    const fieldId = `vd-${optionKey}${subKey ? '-' + subKey : ''}`;
    const type = field.type === 'checkbox' ? 'boolean' : field.type;
    const isShareImage = optionKey === 'share_image';

    if (type === 'media') {
      const [preview, setPreview] = useState('');

      useEffect(() => {
        if (!value) {
          setPreview('');
          return;
        }
        // Jika sudah berupa URL string, pakai langsung
        if (typeof value === 'string' && value.match(/^https?:\/\//i)) {
          setPreview(value);
          return;
        }
        // Jika berupa ID numerik, fetch URL via wp.media jika tersedia
        if (typeof value === 'number' || /^[0-9]+$/.test(String(value))) {
          const attachment = wp.media && wp.media.attachment ? wp.media.attachment(value) : null;
          if (attachment && attachment.fetch) {
            attachment.fetch().then(() => {
              const att = attachment.toJSON();
              const url = (att.sizes && att.sizes.medium && att.sizes.medium.url) || att.url || '';
              if (url) setPreview(url);
            });
          }
        }
      }, [value]);

      const openMedia = (event) => {
        event.preventDefault();
        if (!window.wp || !wp.media) return;
        const frame = wp.media({
          title: 'Pilih Gambar',
          button: { text: 'Gunakan gambar ini' },
          multiple: false,
        });
        frame.on('select', () => {
          const attachment = frame.state().get('selection').first().toJSON();
          setPreview((attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url) || attachment.url || '');
          onChange(optionKey, attachment.id, subKey);
        });
        frame.open();
      };

      const removeMedia = (event) => {
        event.preventDefault();
        setPreview('');
        onChange(optionKey, '', subKey);
      };

      return h(
        'div',
        { className: 'vd-field vd-media-field' },
        [
          h('label', { className: 'vd-label', htmlFor: fieldId }, field.label || field.key),
          h('div', { className: 'vd-media-preview' },
            preview
              ? h('img', { src: preview, alt: '' })
              : h('span', { className: 'vd-media-placeholder' }, 'Belum ada gambar yang dipilih.')
          ),
          h('div', { className: 'vd-media-actions' }, [
            h('button', { type: 'button', className: 'button', onClick: openMedia }, 'Pilih Gambar'),
            value ? h('button', { type: 'button', className: 'button vd-media-remove', onClick: removeMedia }, 'Hapus') : null,
          ]),
          description,
        ]
      );
    }

    if (isShareImage) {
      const openMedia = (event) => {
        event.preventDefault();
        if (!window.wp || !wp.media) return;
        const frame = wp.media({
          title: 'Pilih Gambar',
          button: { text: 'Gunakan gambar ini' },
          multiple: false,
        });
        frame.on('select', () => {
          const attachment = frame.state().get('selection').first().toJSON();
          onChange(optionKey, attachment.url, subKey);
        });
        frame.open();
      };

      const removeMedia = (event) => {
        event.preventDefault();
        onChange(optionKey, '', subKey);
      };

      return h(
        'div',
        { className: 'vd-field vd-media-field' },
        [
          h('label', { className: 'vd-label', htmlFor: fieldId }, field.label || field.key),
          h('div', { className: 'vd-media-preview' },
            value
              ? h('img', { src: value, alt: '' })
              : h('span', { className: 'vd-media-placeholder' }, 'Belum ada gambar yang dipilih.')
          ),
          h('div', { className: 'vd-media-actions' }, [
            h('button', { type: 'button', className: 'button', onClick: openMedia }, 'Pilih Gambar'),
            value ? h('button', { type: 'button', className: 'button vd-media-remove', onClick: removeMedia }, 'Hapus') : null,
          ]),
          description,
        ]
      );
    }

    if (type === 'boolean') {
      return h(
        'div',
        { className: 'vd-field vd-toggle-field' },
        [
          h(
            'label',
            { className: 'vd-toggle', htmlFor: fieldId },
            [
              h('input', {
                id: fieldId,
                type: 'checkbox',
                checked: !!value,
                onChange: (event) => onChange(optionKey, event.target.checked, subKey),
              }),
              h('span', { className: 'vd-toggle-slider' }),
              h('span', { className: 'vd-toggle-label' }, field.label || field.key),
            ]
          ),
          description,
        ]
      );
    }

    if (type === 'array') {
      const choices = Array.isArray(field.choices) ? field.choices : [];
      const currentValues = Array.isArray(value) ? value : [];

      return h(
        'div',
        { className: 'vd-field' },
        [
          h('div', { className: 'vd-label' }, field.label || field.key),
          h(
            'div',
            { className: 'vd-multicheck' },
            choices.map((choice) => {
              const id = `${fieldId}-${choice}`;
              return h(
                'label',
                { key: id, htmlFor: id, className: 'vd-multicheck-item' },
                [
                  h('input', {
                    id,
                    type: 'checkbox',
                    checked: currentValues.includes(choice),
                    onChange: (event) => {
                      const checked = event.target.checked;
                      const next = new Set(currentValues);
                      if (checked) {
                        next.add(choice);
                      } else {
                        next.delete(choice);
                      }
                      onChange(optionKey, Array.from(next), subKey);
                    },
                  }),
                  h('span', null, choice),
                ]
              );
            })
          ),
          description,
        ]
      );
    }

    if (type === 'select') {
      const choices = Array.isArray(field.choices) ? field.choices : [];
      return h(
        'div',
        { className: 'vd-field' },
        [
          h('label', { className: 'vd-label', htmlFor: fieldId }, field.label || field.key),
          h(
            'select',
            {
              id: fieldId,
              value: value !== undefined && value !== null ? value : '',
              onChange: (event) => onChange(optionKey, event.target.value, subKey),
            },
            choices.map((choice) =>
              h('option', { key: choice, value: choice }, choice)
            )
          ),
          description,
        ]
      );
    }

    if (type === 'number' || type === 'media') {
      const isEmpty = value === '' || value === null || typeof value === 'undefined';
      const numericValue = isEmpty ? '' : value;
      return h(
        'div',
        { className: 'vd-field' },
        [
          h('label', { className: 'vd-label', htmlFor: fieldId }, field.label || field.key),
          h('input', {
            id: fieldId,
            type: 'number',
            min: 0,
            value: numericValue,
            onChange: (event) => {
              const nextVal = event.target.value === '' ? '' : Number(event.target.value);
              onChange(optionKey, nextVal, subKey);
            },
          }),
          type === 'media'
            ? h('p', { className: 'vd-desc' }, 'Gunakan Attachment ID media.')
            : null,
          description,
        ]
      );
    }

    const controlProps = {
      id: fieldId,
      value: value !== undefined && value !== null ? value : '',
      placeholder: field.placeholder || undefined,
      onChange: (event) => onChange(optionKey, event.target.value, subKey),
    };

    const inputControl =
      type === 'textarea'
        ? h('textarea', Object.assign({ rows: 3 }, controlProps))
        : h('input', Object.assign({ type: type === 'password' ? 'password' : 'text' }, controlProps));

    return h(
      'div',
      { className: 'vd-field' },
      [
        h('label', { className: 'vd-label', htmlFor: controlProps.id }, field.label || field.key),
        inputControl,
        description,
      ]
    );
  };

  const NewsImport = ({ routes, strings }) => {
    const hasRoute = routes && routes.news;

    const [targets, setTargets] = useState([]);
    const [wpCats, setWpCats] = useState([]);
    const [target, setTarget] = useState('');
    const [category, setCategory] = useState('');
    const [count, setCount] = useState(5);
    const [status, setStatus] = useState('publish');
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
      if (!hasRoute) return;
      apiFetch({ path: routes.news })
        .then((response) => {
          setTargets(Array.isArray(response.data) ? response.data : []);
        })
        .catch(() => setTargets([]));

      apiFetch({ path: '/wp/v2/categories?per_page=100&_fields=id,name' })
        .then((cats) => setWpCats(Array.isArray(cats) ? cats : []))
        .catch(() => setWpCats([]));
    }, []);

    const handleImport = (event) => {
      event.preventDefault();
      setLoading(true);
      setError('');
      setResult('');

      apiFetch({
        path: routes.news,
        method: 'POST',
        data: {
          target,
          category,
          count,
          status,
        },
      })
        .then((res) => {
          setResult(res && res.html ? res.html : strings.importDone);
        })
        .catch(() => setError(strings.importFailed))
        .finally(() => setLoading(false));
    };

    if (!hasRoute) return null;

    return h(
      'div',
      { className: 'vd-card', style: { marginTop: '12px' } },
      [
        h('h3', { className: 'vd-title', style: { marginBottom: '12px' } }, 'Import Artikel'),
        h(
              'div',
              { className: layout === 'list' ? 'vd-list' : 'vd-grid' },
          [
            h('div', { className: 'vd-grid-item' },
              h(OptionField, {
                field: { id: 'news_target', key: 'news_target', label: 'Ambil Target', type: 'select', choices: targets.map((t) => `${t.id}::${t.name || t.id}`), default: '' },
                value: target,
                onChange: (_, val) => {
                  const [id] = String(val || '').split('::');
                  setTarget(id || '');
                },
              })
            ),
            h('div', { className: 'vd-grid-item' },
              h(OptionField, {
                field: { id: 'news_category', key: 'news_category', label: 'Tujuan Kategori', type: 'select', choices: wpCats.map((c) => `${c.id}::${c.name}`), default: '' },
                value: category ? `${category}` : '',
                onChange: (_, val) => {
                  const [id] = String(val || '').split('::');
                  setCategory(id || '');
                },
              })
            ),
            h('div', { className: 'vd-grid-item' },
              h(OptionField, {
                field: { id: 'news_count', key: 'news_count', label: 'Jumlah Artikel', type: 'number', default: 5 },
                value: count,
                onChange: (_, val) => setCount(val === '' ? '' : Number(val)),
              })
            ),
            h('div', { className: 'vd-grid-item' },
              h(OptionField, {
                field: { id: 'news_status', key: 'news_status', label: 'Status', type: 'select', choices: ['publish', 'draft'], default: 'publish' },
                value: status,
                onChange: (_, val) => setStatus(val),
              })
            ),
          ]
        ),
        h(
          'div',
          { style: { marginTop: '12px' } },
          h('button', { type: 'button', className: 'button button-primary', onClick: handleImport, disabled: loading || !target || !category || !count || !status },
            loading ? strings.importing : strings.import
          )
        ),
        error ? h('div', { className: 'notice notice-error', style: { marginTop: '12px' } }, h('p', null, error)) : null,
        result ? h('div', { className: 'vd-import-result', style: { marginTop: '12px' }, dangerouslySetInnerHTML: { __html: result } }) : null,
      ]
    );
  };

  const StatisticsPanel = ({ routes }) => {
    if (!routes || !routes.statistics) return null;

    const [data, setData] = useState(null);
    const [error, setError] = useState('');

    useEffect(() => {
      apiFetch({ path: routes.statistics })
        .then((res) => setData(res))
        .catch(() => setError('Gagal memuat statistik.'));
    }, []);

    if (error) return h('div', { className: 'notice notice-error' }, h('p', null, error));
    if (!data) return h('div', { className: 'vd-card' }, h('p', null, 'Memuat statistik...'));

    const summary = data.summary || {};
    const daily = data.daily || [];
    const pages = data.pages || [];
    const refs = data.referrer || [];

    return h(
      'div',
      null,
      [
        h('div', { className: 'vd-card' },
          [
            h('h3', { className: 'vd-title' }, 'Ringkasan'),
            h('div', { className: 'vd-grid' },
              Object.entries(summary).map(([label, obj]) =>
                h('div', { key: label, className: 'vd-grid-item' },
                  h('div', { className: 'vd-field' }, [
                    h('div', { className: 'vd-label' }, label),
                    h('div', null, `Pengunjung unik: ${obj.unique_visitors ?? 0}`),
                    h('div', null, `Total visits: ${obj.total_visits ?? 0}`),
                  ])
                )
              )
            ),
          ]
        ),
        h('div', { className: 'vd-card' },
          [
            h('h3', { className: 'vd-title' }, 'Top Pages (30 hari)'),
            h('div', { className: 'vd-table-wrap' },
              h('table', { className: 'widefat striped' },
                [
                  h('thead', null,
                    h('tr', null, [
                      h('th', null, 'URL'),
                      h('th', null, 'Views'),
                    ])
                  ),
                  h('tbody', null,
                    pages.length
                      ? pages.map((p, idx) =>
                          h('tr', { key: idx }, [
                            h('td', null, p.url),
                            h('td', null, p.views),
                          ])
                        )
                      : h('tr', null, h('td', { colSpan: 2 }, 'Tidak ada data'))
                  ),
                ]
              )
            ),
          ]
        ),
        h('div', { className: 'vd-card' },
          [
            h('h3', { className: 'vd-title' }, 'Top Referrer (30 hari)'),
            h('div', { className: 'vd-table-wrap' },
              h('table', { className: 'widefat striped' },
                [
                  h('thead', null,
                    h('tr', null, [
                      h('th', null, 'Referrer'),
                      h('th', null, 'Visits'),
                    ])
                  ),
                  h('tbody', null,
                    refs.length
                      ? refs.map((r, idx) =>
                          h('tr', { key: idx }, [
                            h('td', null, r.referer),
                            h('td', null, r.visits),
                          ])
                        )
                      : h('tr', null, h('td', { colSpan: 2 }, 'Tidak ada data'))
                  ),
                ]
              )
            ),
          ]
        ),
        h('div', { className: 'vd-card' },
          [
            h('h3', { className: 'vd-title' }, 'Daily Visits (30 hari)'),
            h('div', { className: 'vd-table-wrap' },
              h('table', { className: 'widefat striped' },
                [
                  h('thead', null,
                    h('tr', null, [
                      h('th', null, 'Tanggal'),
                      h('th', null, 'Unique'),
                      h('th', null, 'Total'),
                    ])
                  ),
                  h('tbody', null,
                    daily.length
                      ? daily.map((d, idx) =>
                          h('tr', { key: idx }, [
                            h('td', null, d.date),
                            h('td', null, d.unique_visits),
                            h('td', null, d.total_visits),
                          ])
                        )
                      : h('tr', null, h('td', { colSpan: 3 }, 'Tidak ada data'))
                  ),
                ]
              )
            ),
          ]
        ),
      ]
    );
  };

  const OptimizePanel = ({ routes }) => {
    const hasRoute = routes && routes.optimize;

    const [data, setData] = useState(null);
    const [selected, setSelected] = useState([]);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const chartRef = useRef(null);
    const chartInstance = useRef(null);

    useEffect(() => {
      if (!hasRoute) return;
      apiFetch({ path: routes.optimize })
        .then((res) => setData(res))
        .catch(() => setError('Gagal memuat data optimasi.'));
    }, [hasRoute]);

    const toggleItem = (key) => {
      setSelected((prev) => {
        const next = new Set(prev);
        if (next.has(key)) {
          next.delete(key);
        } else {
          next.add(key);
        }
        return Array.from(next);
      });
    };

    const handleOptimize = () => {
      if (!selected.length) return;
      setLoading(true);
      setMessage('');
      setError('');
      apiFetch({
        path: routes.optimize,
        method: 'POST',
        data: { items: selected },
      })
        .then(() => {
          setMessage('Optimize selesai.');
          // refresh stats
          return apiFetch({ path: routes.optimize });
        })
        .then((res) => setData(res))
        .catch(() => setError('Gagal menjalankan optimize.'))
        .finally(() => setLoading(false));
    };

    if (!hasRoute) return null;
    if (error) return h('div', { className: 'notice notice-error' }, h('p', null, error));
    if (!data) return h('div', { className: 'vd-card' }, h('p', null, 'Memuat data optimasi...'));

    const stats = data.stats || {};
    const labelsMap = {
      revisions: 'Revisions',
      auto_drafts: 'Auto Draft',
      trash_posts: 'Posts di Trash',
      orphan_postmeta: 'Orphan Postmeta',
      orphan_term_rel_object: 'Orphan Term Relationships (Object)',
      orphan_term_rel_tax: 'Orphan Term Relationships (Taxonomy)',
      orphan_termmeta: 'Orphan Termmeta',
      comments_spam_trash: 'Komentar Spam & Trash',
      comments_pending_old: 'Komentar Pending > 90 Hari',
      orphan_commentmeta: 'Orphan Commentmeta',
      expired_transients: 'Transients Kedaluwarsa',
      oembed_cache: 'Cache oEmbed',
    };

    const formatBytes = (bytes) => {
      const units = ['B', 'KB', 'MB', 'GB'];
      let size = parseFloat(bytes || 0);
      let i = 0;
      while (size >= 1024 && i < units.length - 1) {
        size /= 1024;
        i++;
      }
      return `${size % 1 === 0 ? size : size.toFixed(2)} ${units[i]}`;
    };

    const totalRows = Object.values(stats).reduce((sum, it) => sum + (it.count || 0), 0);
    const totalSize = Object.values(stats).reduce((sum, it) => sum + (it.size || 0), 0);

    const topBySize = Object.entries(stats)
      .map(([key, it]) => ({ key, size: it.size || 0, count: it.count || 0 }))
      .sort((a, b) => b.size - a.size)
      .slice(0, 3)
      .filter((it) => it.size > 0 || it.count > 0);

    return h(
      'div',
      { className: 'vd-card' },
      [
        h('h3', { className: 'vd-title' }, 'Optimize Database'),
        h('p', { className: 'vd-subtitle' }, `Statistik Kandidat: ${totalRows} row, ${formatBytes(totalSize)}`),
        topBySize.length
          ? h('div', { className: 'vd-field' }, [
              h('strong', null, 'Top berdasarkan ukuran:'),
              h('ul', { className: 'vd-desc' },
                topBySize.map((it) =>
                  h('li', { key: it.key }, `${labelsMap[it.key] || it.key}: ${formatBytes(it.size)} (${it.count} row)`)
                )
              ),
            ])
          : null,
        h('div', { className: 'vd-grid vd-grid-2col', style: { marginTop: '10px' } }, [
          h('div', { className: 'vd-grid-item' },
            h(
              'div',
              { className: 'vd-table-wrap' },
              h('table', { className: 'widefat striped' }, [
                h('thead', null,
                  h('tr', null, [
                    h('th', { style: { width: '40px' } }, 'Pilih'),
                    h('th', null, 'Item'),
                    h('th', { style: { width: '80px' } }, 'Row'),
                    h('th', { style: { width: '120px' } }, 'Ukuran'),
                  ])
                ),
                h('tbody', null,
                  Object.entries(stats).map(([key, item]) => {
                    const label = labelsMap[key] || key;
                    const rows = item.count || 0;
                    const sizeText = formatBytes(item.size || 0);
                    return h('tr', { key }, [
                      h('td', null,
                        h('input', {
                          type: 'checkbox',
                          checked: selected.includes(key),
                          onChange: () => toggleItem(key),
                        })
                      ),
                      h('td', null, label),
                      h('td', null, `${rows} row`),
                      h('td', null, sizeText),
                    ]);
                  })
                ),
              ])
            )
          ),
          h('div', { className: 'vd-grid-item' },
            h('div', { className: 'vd-field' }, [
              h('strong', null, 'Penjelasan & Dampak'),
              h('ul', { className: 'vd-desc' }, [
                h('li', null, 'Kolom "Row" menampilkan jumlah row yang akan dihapus; "Estimasi Ukuran" adalah perkiraan total byte konten terkait.'),
                h('li', null, 'Revisions, Auto Draft, Trash: membersihkan cadangan/konsep; aman untuk konten publik.'),
                h('li', null, 'Orphan Postmeta/Term/Relasi/Commentmeta: hanya menghapus data tanpa induk, aman.'),
                h('li', null, 'Komentar Spam/Trash/Pending > 90 Hari: mengurangi bloat moderasi.'),
                h('li', null, 'Transients Kedaluwarsa: mengosongkan cache yang sudah lewat waktu; cache akan terisi ulang.'),
                h('li', null, 'Cache oEmbed: cache akan digenerasi saat konten diakses.'),
                h('li', null, 'Kompatibilitas: tidak menyentuh meta/page builder; fokus pada data yatim/cadangan/sampah/cache.'),
              ]),
            ])
          ),
        ]),
        h('div', { style: { marginTop: '12px' } },
          h('button', { type: 'button', className: 'button button-primary', disabled: loading || !selected.length, onClick: handleOptimize }, loading ? 'Mengoptimalkan...' : 'Optimize')
        ),
        message ? h('div', { className: 'notice notice-success', style: { marginTop: '10px' } }, h('p', null, message)) : null,
      ]
    );
  };

  const App = () => {
    const defaultTab = tabs.length ? tabs[0].id : 'all';
    const [values, setValues] = useState(baseValues);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const [activeTab, setActiveTab] = useState(defaultTab);

    useEffect(() => {
      apiFetch({ path: appData.routes.options })
        .then((response) => {
          if (response && response.options) {
            setValues(Object.assign({}, baseValues, response.options));
          }
          setLoading(false);
        })
        .catch(() => {
          setError(strings.loadError);
          setLoading(false);
        });
    }, []);

    const handleChange = (optionKey, nextValue, subKey = null) => {
      setValues((prev) => {
        const next = Object.assign({}, prev);
        if (subKey) {
          const nested = typeof next[optionKey] === 'object' && next[optionKey] !== null ? Object.assign({}, next[optionKey]) : {};
          nested[subKey] = nextValue;
          next[optionKey] = nested;
          return next;
        }

        next[optionKey] = nextValue;
        return next;
      });
    };

    const handleSubmit = (event) => {
      event.preventDefault();
      setSaving(true);
      setMessage('');
      setError('');

      apiFetch({
        path: appData.routes.options,
        method: 'POST',
        data: values,
      })
        .then((response) => {
          if (response && response.options) {
            setValues(Object.assign({}, baseValues, response.options));
          }
          setMessage(strings.updated);
        })
        .catch(() => {
          setError(strings.saveError);
        })
        .finally(() => setSaving(false));
    };

    if (loading) {
      return h('div', { className: 'vd-card' }, h('p', null, 'Memuat pengaturan...'));
    }

    const fieldsToRenderRaw = tabs.length
      ? (() => {
          const currentTab = tabs.find((tab) => tab.id === activeTab) || null;
          const keys = currentTab && Array.isArray(currentTab.fieldKeys) ? currentTab.fieldKeys : [];
          return keys.map((key) => fieldMap[key]).filter(Boolean);
        })()
      : fields;

    const fieldsToRender = fieldsToRenderRaw && fieldsToRenderRaw.length ? fieldsToRenderRaw : fields;
    const hasFields = fieldsToRender.length > 0;

    return h(
      Fragment,
      null,
      [
        error
          ? h('div', { className: 'notice notice-error' }, h('p', null, error))
          : null,
        message
          ? h('div', { className: 'notice notice-success' }, h('p', null, message))
          : null,
        hasFields
          ? h(
              'form',
              { className: 'vd-card', onSubmit: handleSubmit },
              [
                h(
                  'div',
                  { className: 'vd-card-header' },
                  [
                    h('div', null, [
                      h('h2', { className: 'vd-title' }, strings.title),
                      h('p', { className: 'vd-subtitle' }, strings.subtitle),
                    ]),
                    h(
                      'div',
                      { className: 'vd-card-actions' },
                      [
                        h(
                          'button',
                          {
                            type: 'submit',
                            className: 'button button-primary',
                            disabled: saving,
                          },
                          saving ? strings.saving : strings.save
                        ),
                      ]
                    ),
                  ]
                ),
                tabs.length > 1
                  ? h(
                      'div',
                      { className: 'nav-tab-wrapper vd-react-tabs' },
                      tabs.map((tab) =>
                        h(
                          'a',
                          {
                            href: '#',
                            className: 'nav-tab' + (tab.id === activeTab ? ' nav-tab-active' : ''),
                            onClick: (e) => {
                              e.preventDefault();
                              setActiveTab(tab.id);
                            },
                          },
                          tab.title || tab.id
                        )
                      )
                    )
                  : null,
                h(
                  'div',
                  { className: layout === 'list' ? 'vd-list' : 'vd-grid' },
                  fieldsToRender.map((field) => {
                    const optionKey = field.id || field.key;
                    const subKey = field.sub || null;
                    const fieldValue = subKey
                      ? (values[optionKey] || {})[subKey]
                      : values[optionKey];

                    const itemKey = field.key || `${optionKey}${subKey ? '__' + subKey : ''}`;
                    return h(
                      'div',
                      { key: itemKey, className: 'vd-grid-item' },
                      h(OptionField, {
                        field,
                        value: fieldValue,
                        onChange: handleChange,
                      })
                    );
                  })
                ),
              ]
            )
          : null,
        h(NewsImport, { routes: appData.routes, strings }),
        h(StatisticsPanel, { routes: appData.routes }),
        h(OptimizePanel, { routes: appData.routes }),
      ]
    );
  };

  document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById(rootId);
    if (root) {
      render(h(App), root);
    }
  });
})();
