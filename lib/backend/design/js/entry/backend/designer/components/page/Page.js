import React from 'react';
import { connect } from 'react-redux';

class Page extends React.Component {

    constructor(props) {
        super(props);

    }

    render() {
        return (
            <div>
                Page
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps) => ({
    //addTheme: state.topButtons.addTheme,
});

const mapDispatchToProps = (dispatch, ownProps) => ({
    /*addThemeButton: toggle => dispatch({
        type: toggle === 'show' ? 'SHOW_BUTTON' : 'HIDE_BUTTON',
        button: 'addTheme'
    }),*/
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Page)