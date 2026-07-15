public class ConcessionItemFactory {

    public static ConcessionItem createPopcorn(String size) {
        return new Popcorn(size, 1);
    }

    public static ConcessionItem createDrink(String type) {
        return new Drink(type, 1);
    }

    public static ConcessionItem createCombo(String type) {
        return new Combo(type, 1);
    }
}
