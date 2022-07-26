import React from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import PopUp from './PopUp';
import Tools from './Tools';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faWindowMinimize } from '@fortawesome/free-regular-svg-icons';
import { saveSetting, saveSubSetting, closeTool, fromPopUp } from '../reducers/designer.actions';

class ToolWindows extends React.Component {
    constructor(props) {
        super(props);

    }

    componentDidMount() {
    }

    componentWillUnmount() {
    }

    moveToGrid(name) {
        return (
            <span className="btn-move-to-grid" key="move-to-grid" onClick={() => this.props.moveToGrid(name)}>
                <FontAwesomeIcon icon={faWindowMinimize} />
            </span>
        );
    }

    render() {
        return (
            <>
                {this.props.toolsList.map(item => (
                    <PopUp close={() => this.props.closeTool(item.name)} key={item.name} name={item.name}>
                        <PopUp.Header icons={[this.moveToGrid(item.name)]}>{item.name}</PopUp.Header>
                        <PopUp.Content>
                            <Tools name={item.name} areaName="toolWindows"/>
                        </PopUp.Content>
                    </PopUp>
                ))}
            </>
        );
    }
};

const mapStateToProps = (state, ownProps) => {
    return {
        toolsList: state.designer.toolsList.toolWindows ? state.designer.toolsList.toolWindows : [],
    }};

const mapDispatchToProps = (dispatch, ownProps) => ({
    saveSubSettings: (name, subName, value) => dispatch(saveSubSetting(name, subName, value)),
    saveSetting: (name, value) => dispatch(saveSetting(name, value)),
    closeTool: (toolName) => dispatch(closeTool('toolWindows', toolName)),
    moveToGrid: (toolName) => dispatch(fromPopUp(toolName)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(ToolWindows)