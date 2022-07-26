import React from 'react';
import Themes from './themes/Themes';
import Page from './page/Page';

export default function(props) {
    switch (props.name) {
        case 'Themes': return <Themes areaName={props.areaName}/>;
        case 'Page': return <Page />;
        default: return <div>{props.name}</div>
    }
}