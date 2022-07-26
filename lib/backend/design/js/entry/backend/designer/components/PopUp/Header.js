import React from 'react';

export default class Header extends React.Component {
    constructor(props) {
        super(props);

    }

    render() {
        return (
            <div className="popup-heading">
                {this.props.icons && Array.isArray(this.props.icons) && this.props.icons.length > 0 ? (
                    <div className="icons">
                        {this.props.icons.map(item => item)}
                    </div>
                ) : ''}
                {this.props.children}
            </div>
        )
    }
};