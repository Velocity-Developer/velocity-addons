import React, { useEffect, useMemo, useState } from 'react';
import { createApi } from './api';
import Tabs from './components/Tabs';
import Notice from './components/Notice';
import GeneralTab from './tabs/GeneralTab';
import SecurityTab from './tabs/SecurityTab';
import LicenseTab from './tabs/LicenseTab';
import CustomTab from './tabs/CustomTab';

const TAB_LIST = [
  { id: 'general', label: 'General' },
  { id: 'security', label: 'Security' },
  { id: 'license', label: 'License' },
  { id: 'custom', label: 'Custom' },
];

const App = ({ bootstrap }) => {
  const [activeTab, setActiveTab] = useState(TAB_LIST[0].id);
  const [values, setValues] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState(null);

  const api = useMemo(
    () =>
      createApi({
        nonce: bootstrap.nonce,
        restUrl: bootstrap.restUrl,
        optionsEndpoint: bootstrap.optionsEndpoint || '/velocity-addons/v1/options',
      }),
    [bootstrap]
  );

  useEffect(() => {
    api
      .getOptions()
      .then((res) => {
        setValues(res.options || {});
      })
      .catch(() => setNotice({ type: 'error', message: 'Gagal memuat data.' }))
      .finally(() => setLoading(false));
  }, [api]);

  const updateValue = (key, val) => setValues((prev) => ({ ...prev, [key]: val }));

  const handleSave = async () => {
    setSaving(true);
    setNotice(null);
    try {
      const res = await api.saveOptions(values);
      setValues(res.options || values);
      setNotice({ type: 'success', message: 'Pengaturan berhasil disimpan.' });
    } catch (e) {
      setNotice({ type: 'error', message: 'Gagal menyimpan pengaturan.' });
    } finally {
      setSaving(false);
    }
  };

  const renderTab = () => {
    switch (activeTab) {
      case 'general':
        return <GeneralTab values={values} onChange={updateValue} />;
      case 'security':
        return <SecurityTab values={values} onChange={updateValue} />;
      case 'license':
        return <LicenseTab values={values} onChange={updateValue} />;
      case 'custom':
        return <CustomTab values={values} onChange={updateValue} />;
      default:
        return null;
    }
  };

  if (loading) return <div className="vd-card"><p>Memuat...</p></div>;

  return (
    <div className="vd-card">
      <div className="vd-card-header">
        <div>
          <h2 className="vd-title">Plugin Settings</h2>
          <p className="vd-subtitle">Kelola opsi Velocity Addons.</p>
        </div>
        <div>
          <button className="button button-primary" onClick={handleSave} disabled={saving}>
            {saving ? 'Menyimpan...' : 'Simpan'}
          </button>
        </div>
      </div>

      {notice && <Notice type={notice.type} message={notice.message} />}

      <Tabs items={TAB_LIST} activeId={activeTab} onChange={setActiveTab} />

      <div className="vd-panel">{renderTab()}</div>
    </div>
  );
};

export default App;
