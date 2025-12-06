import React from 'react';
import FieldInput from '../components/FieldInput';
import FieldCheckbox from '../components/FieldCheckbox';

const LicenseTab = ({ values, onChange }) => (
  <div className="vd-grid">
    <FieldInput
      label="License Key"
      value={values.license_key}
      onChange={(val) => onChange('license_key', val)}
      placeholder="Masukkan license key"
    />
    <FieldCheckbox
      label="Aktifkan Auto-Update"
      checked={values.auto_update}
      onChange={(val) => onChange('auto_update', val)}
    />
  </div>
);

export default LicenseTab;
