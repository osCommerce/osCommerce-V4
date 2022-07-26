import React from 'react';
import { connect } from 'react-redux';
import globals from 'src/globals';
import { toggleWindow } from '../../reducers/toolWindows.actions';

class CreateThemeButton extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        return this.props.addTheme
            ?
            <span className={'btn btn-primary' + (this.props.active ? ' active' : '')} onClick={this.props.onClick}>{globals(['tr', 'TEXT_ADD_THEME'])}</span>
            : ''
        ;
    }
}


const mapStateToProps = (state, ownProps) => ({
    addTheme: state.topButtons.addTheme,
    active: state.toolWindows.addTheme,
});

const mapDispatchToProps = (dispatch, ownProps) => ({
    onClick: () => dispatch(toggleWindow('addTheme')),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(CreateThemeButton)