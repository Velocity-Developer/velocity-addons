import React from 'react';
import FieldInput from '../components/FieldInput';
import FieldSelect from '../components/FieldSelect';

const CustomTab = ({ values, onChange }) => (
  <div className="vd-grid">
    <FieldInput
      label="Custom Script URL"
      value={values.custom_script_url}
      onChange={(val) => onChange('custom_script_url', val)}
      placeholder="https://..."
    />
    <FieldSelect
      label="Environment"
      value={values.environment}
      onChange={(val) => onChange('environment', val)}
      options={[
        { value: 'dev', label: 'Development' },
        { value: 'staging', label: 'Staging' },
        { value: 'prod', label: 'Production' },
      ]}
    />
  </div>
);

export default CustomTab;
