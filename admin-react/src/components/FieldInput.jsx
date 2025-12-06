import React from 'react';

const FieldInput = ({ label, value, onChange, type = 'text', placeholder }) => (
  <div className="vd-field">
    <label className="vd-label">{label}</label>
    <input
      type={type}
      value={value || ''}
      placeholder={placeholder}
      onChange={(e) => onChange(e.target.value)}
    />
  </div>
);

export default FieldInput;
