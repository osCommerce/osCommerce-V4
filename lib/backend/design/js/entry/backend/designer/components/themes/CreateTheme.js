import React from 'react';
import "./themes.scss";
import fetchData from 'src/fetch-data';
import globals from 'src/globals';
import { connect } from 'react-redux';
import PopUp from '../PopUp';
import {Form, Col, Row, Button} from 'react-bootstrap';
import Alert from 'react-bootstrap/Alert';
import { saveSetting } from '../../reducers/designer.actions';
import { toggleWindow } from '../../reducers/toolWindows.actions';

class Themes extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            themeSource: 'theme',
            warning: false
        };

        this.handleChange = this.handleChange.bind(this)
        this.handleSubmit = this.handleSubmit.bind(this)
    }

    handleChange (event){
        this.setState({[event.target.name]: event.target.value})
    }

    handleSubmit(event) {
        const props = this.props;
        const componentObj = this;

        fetchData('themes', 'add-theme', this.state, {method: 'POST'})
            .then((response) =>  JSON.parse(response))
            .then(response => {
                if (+response.code === 406) {
                    componentObj.setState({warning: response.text})
                } else if (+response.code === 200) {
                    props.saveSetting('themes', response.themes);
                    componentObj.setState({warning: false});
                    props.closePopUp()
                } else {
                    componentObj.setState({warning: "Error"})
                }
            });

        event.preventDefault();
    }

    render() {
        return this.props.showWindow ? (
            <PopUp close={() => this.props.closePopUp()} name="createTheme">
                <PopUp.Header>Create Theme</PopUp.Header>
                <PopUp.Content>

                    {this.state.warning ? (
                        <Alert variant="warning">
                            {this.state.warning}
                        </Alert>
                    ) : ''}

                    <Form.Group as={Row} controlId="title">
                        <Form.Label column sm={3}>
                            Theme title
                        </Form.Label>
                        <Col sm={9}>
                            <Form.Control
                                type="text"
                                name="title"
                                placeholder="Theme title"
                                onKeyUp={this.handleChange}
                                onChange={this.handleChange}
                            />
                        </Col>
                    </Form.Group>

                    <Form.Group as={Row}>
                        <Form.Label as="legend" column sm={3}>
                            Theme Source
                        </Form.Label>
                        <Col sm={9}>
                            <Form.Check
                                type="radio"
                                label="Empty Theme"
                                name="themeSource"
                                value="empty"
                                checked={this.state.themeSource === 'empty'}
                                id="themeSourceEmpty"
                                onChange={this.handleChange}
                            />
                            <Form.Check
                                type="radio"
                                label="Copy from an existing theme"
                                name="themeSource"
                                value="theme"
                                checked={this.state.themeSource === 'theme'}
                                id="themeSourceTheme"
                                onChange={this.handleChange}
                            />
                            <Form.Check
                                type="radio"
                                label="Upload from computer"
                                name="themeSource"
                                value="computer"
                                checked={this.state.themeSource === 'computer'}
                                id="themeSourceComputer"
                                onChange={this.handleChange}
                            />
                            <Form.Check
                                type="radio"
                                label="Upload from URL"
                                name="themeSource"
                                value="url"
                                checked={this.state.themeSource === 'url'}
                                id="themeSourceUrl"
                                onChange={this.handleChange}
                            />
                        </Col>
                    </Form.Group>

                    {this.state.themeSource === 'theme' ? (
                        <Form.Group as={Row}>
                            <Form.Label column sm={3}>
                            </Form.Label>
                            <Col sm={9}>
                                <Form.Control
                                    as="select"
                                    name="parent_theme"
                                    onChange={this.handleChange}
                                >
                                    {this.props.themes.map(theme => (
                                        <option
                                            value={theme.theme_name}
                                            key={theme.theme_name}>
                                            {theme.title}
                                        </option>
                                    ))}
                                </Form.Control>
                            </Col>
                        </Form.Group>
                    ) : ''}

                    {this.state.themeSource === 'url' ? (
                    <Form.Group as={Row}>
                        <Form.Label column sm={3}>
                        </Form.Label>
                        <Col sm={9}>
                            <Form.Control
                                type="text"
                                placeholder="Url"
                                name="url"
                                onChange={this.handleChange}
                            />
                        </Col>
                    </Form.Group>
                    ) : ''}

                </PopUp.Content>
                <PopUp.Footer>
                    <div className="row">
                        <Col sm={{ span: 6 }}>
                            <Button
                                size="sm"
                                variant="secondary"
                                onClick={() => this.props.closePopUp()}
                            >Close</Button>
                        </Col>
                        <Col sm={{ span: 6 }} className="text-right">
                            <Button
                                size="sm"
                                onClick={this.handleSubmit}
                            >Create</Button>
                        </Col>
                    </div>
                </PopUp.Footer>
            </PopUp>
        ) : '';
    }
}


const mapStateToProps = (state, ownProps) => ({
    showWindow: state.toolWindows.addTheme,
    themes: state.designer.themes,
});

const mapDispatchToProps = (dispatch, ownProps) => ({
    closePopUp: () => dispatch(toggleWindow('addTheme')),
    saveSetting: (name, value) => dispatch(saveSetting(name, value)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Themes)