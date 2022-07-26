import React from 'react';

export default class Footer extends React.Component {
    constructor(props) {
        super(props);

    }

    render() {
        return <div className="popup-footer">{this.props.children}</div>
    }
};