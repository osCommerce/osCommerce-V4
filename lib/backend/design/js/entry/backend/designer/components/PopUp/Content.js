import React from 'react';

export default class Content extends React.Component {
    constructor(props) {
        super(props);

    }

    render() {
        return <div className="popup-content">{this.props.children}</div>
    }
};