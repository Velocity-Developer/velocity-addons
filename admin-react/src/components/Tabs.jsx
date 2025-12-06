import React from 'react';

const Tabs = ({ items, activeId, onChange }) => (
  <div className="vd-tabs">
    {items.map((item) => (
      <button
        key={item.id}
        className={`vd-tab${activeId === item.id ? ' is-active' : ''}`}
        onClick={() => onChange(item.id)}
        type="button"
      >
        {item.label}
      </button>
    ))}
  </div>
);

export default Tabs;
