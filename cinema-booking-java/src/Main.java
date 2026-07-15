import javafx.application.Application;
import javafx.stage.Stage;

public class Main extends Application {

    public static Stage primaryStage;

    @Override
    public void start(Stage stage) {
        primaryStage = stage;
        primaryStage.setTitle("🎬 Cinema Ticket System");
        primaryStage.setResizable(false);
        LoginScreen.show();
    }

    public static void main(String[] args) {
        UserDatabase.initDefaults();
        MovieManager.initDefaults();
        ShowtimeManager.initDefaults();
        launch(args);
    }
}
