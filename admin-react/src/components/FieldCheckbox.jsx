import React from 'react';

const FieldCheckbox = ({ label, checked, onChange, description }) => (
  <label className="vd-field vd-checkbox">
    <input
      type="checkbox"
      checked={!!checked}
      onChange={(e) => onChange(e.target.checked)}
    />
    <span>{label}</span>
    {description && <p className="vd-desc">{description}</p>}
  </label>
);

export default FieldCheckbox;
