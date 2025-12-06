import React from 'react';
import FieldCheckbox from '../components/FieldCheckbox';
import FieldInput from '../components/FieldInput';
import FieldSelect from '../components/FieldSelect';

const GeneralTab = ({ values, onChange }) => (
  <div className="vd-grid">
    <FieldCheckbox
      label="Aktifkan Fitur A"
      checked={values.feature_a}
      onChange={(val) => onChange('feature_a', val)}
    />
    <FieldInput
      label="Judul Situs"
      value={values.site_title}
      onChange={(val) => onChange('site_title', val)}
      placeholder="Contoh: Velocity Site"
    />
    <FieldSelect
      label="Mode Tampilan"
      value={values.display_mode}
      onChange={(val) => onChange('display_mode', val)}
      options={[
        { value: 'light', label: 'Light' },
        { value: 'dark', label: 'Dark' },
      ]}
    />
  </div>
);

export default GeneralTab;
