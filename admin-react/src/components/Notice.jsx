import React from 'react';

const Notice = ({ type = 'info', message }) => (
  <div className={`vd-notice vd-notice-${type}`}>
    {message}
  </div>
);

export default Notice;
