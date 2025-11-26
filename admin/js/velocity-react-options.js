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

  const { createElement: h, Fragment, render, useEffect, useState } = wp.element;
  const apiFetch = wp.apiFetch;
  const fields = Array.isArray(appData.fields) ? appData.fields : [];
  const strings = Object.assign(
    {
      title: 'Pengaturan Velocity (React)',
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

  const OptionField = ({ field, value, onChange }) => {
    const description = field.description ? h('p', { className: 'vd-desc' }, field.description) : null;
    const optionKey = field.id || field.key;
    const subKey = field.sub || null;
    const fieldId = `vd-${optionKey}${subKey ? '-' + subKey : ''}`;
    const type = field.type === 'checkbox' ? 'boolean' : field.type;

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

  const App = () => {
    const [values, setValues] = useState(baseValues);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');

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
        h(
          'form',
          { className: 'vd-card', onSubmit: handleSubmit },
          [
            h(
              'div',
              { className: 'vd-card-header' },
              [
                h('div', null, [
                  h('h2', { className: 'vd-title' }, strings.title),
                  h('p', { className: 'vd-subtitle' }, 'Kelola opsi inti Velocity Addons melalui REST API.'),
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
            h(
              'div',
              { className: 'vd-grid' },
              fields.map((field) => {
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
        ),
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
