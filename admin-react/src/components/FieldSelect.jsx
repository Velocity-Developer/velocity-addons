import React from 'react';

const FieldSelect = ({ label, value, onChange, options }) => (
  <div className="vd-field">
    <label className="vd-label">{label}</label>
    <select value={value || ''} onChange={(e) => onChange(e.target.value)}>
      {options.map((opt) => (
        <option key={opt.value} value={opt.value}>
          {opt.label}
        </option>
      ))}
    </select>
  </div>
);

export default FieldSelect;
